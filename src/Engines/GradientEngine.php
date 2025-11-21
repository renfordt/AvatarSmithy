<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

use Renfordt\AvatarSmithy\Support\Name;

class GradientEngine extends AbstractEngine
{
    /**
     * @param array<string, mixed> $options
     */
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        $nameObj = Name::make($seed);
        $shape = is_string($options['shape'] ?? null) ? $options['shape'] : 'circle';
        $gradientType = is_string($options['gradientType'] ?? null) ? $options['gradientType'] : 'horizontal';

        // Marble type uses a completely different rendering approach
        if (strtolower($gradientType) === 'marble') {
            return $this->generateMarbleAvatar($seed, $nameObj, $size, $options);
        }

        // Generate gradient colors
        $colors = $this->generateGradientColors($nameObj, $options);

        // Create gradient definition with unique ID based on seed AND gradient type
        $gradientId = 'gradient-' . substr(md5($seed . $gradientType), 0, 8);
        $gradientSvg = $this->createGradient($gradientType, $gradientId, $colors, $options);

        // Create shape SVG
        $shapeSvg = $this->createShapeSvg($size, $shape, $gradientId, $options);

        // Build final SVG with defs
        $svg = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
        $svg .= '<defs>' . $gradientSvg . '</defs>';
        $svg .= $shapeSvg;

        return $svg . '</svg>';
    }

    /**
     * @param array<string, mixed> $options
     * @return array<array{offset: float, color: string}>
     */
    protected function generateGradientColors(Name $name, array $options): array
    {
        $baseColor = $name->getHexColor()->toHSL();
        $numColors = is_int($options['colorStops'] ?? null) ? $options['colorStops'] : 3;
        $colors = [];

        for ($i = 0; $i < $numColors; $i++) {
            $hsl = clone $baseColor;
            $factor = $i / max(1, $numColors - 1);

            // Vary hue slightly
            $hsl->setHue((int) ($baseColor->getHue() + ($factor * 60 - 30)));

            // Vary lightness
            $lightness = 0.3 + ($factor * 0.5);
            $hsl->setLightness($lightness);

            // Vary saturation
            $saturation = 0.6 + ($factor * 0.3);
            $hsl->setSaturation($saturation);

            $colors[] = [
                'offset' => $factor * 100,
                'color' => $hsl->toHex()->__toString(),
            ];
        }

        return $colors;
    }

    /**
     * @param array<array{offset: float, color: string}> $colors
     * @param array<string, mixed> $options
     */
    protected function createGradient(string $type, string $id, array $colors, array $options): string
    {
        return match (strtolower($type)) {
            'vertical' => $this->createLinearGradient($id, $colors, 0, 0, 0, 100),
            'diagonal' => $this->createLinearGradient($id, $colors, 0, 0, 100, 100),
            'radial' => $this->createRadialGradient($id, $colors),
            'wavy' => $this->createWavyGradient($id, $colors, $options),
            default => $this->createLinearGradient($id, $colors, 0, 0, 100, 0), // horizontal
        };
    }

    /**
     * @param array<array{offset: float, color: string}> $colors
     */
    protected function createLinearGradient(string $id, array $colors, float $x1, float $y1, float $x2, float $y2): string
    {
        $svg = '<linearGradient id="' . htmlspecialchars($id) . '" x1="' . $x1 . '%" y1="' . $y1 . '%" x2="' . $x2 . '%" y2="' . $y2 . '%">';

        foreach ($colors as $stop) {
            $svg .= '<stop offset="' . $stop['offset'] . '%" stop-color="' . htmlspecialchars((string) $stop['color']) . '"/>';
        }
        return $svg . '</linearGradient>';
    }

    /**
     * @param array<array{offset: float, color: string}> $colors
     */
    protected function createRadialGradient(string $id, array $colors): string
    {
        $svg = '<radialGradient id="' . htmlspecialchars($id) . '" cx="50%" cy="50%" r="50%">';

        foreach ($colors as $stop) {
            $svg .= '<stop offset="' . $stop['offset'] . '%" stop-color="' . htmlspecialchars((string) $stop['color']) . '"/>';
        }
        return $svg . '</radialGradient>';
    }

    /**
     * @param array<array{offset: float, color: string}> $colors
     * @param array<string, mixed> $options
     */
    protected function createWavyGradient(string $id, array $colors, array $options): string
    {
        // For wavy, we'll create a diagonal gradient with multiple color stops
        // to give a more dynamic appearance
        $wavyColors = [];
        foreach ($colors as $i => $color) {
            // Add intermediate stops for wave effect
            if ($i > 0) {
                $prevColor = $colors[$i - 1];
                $midOffset = ($prevColor['offset'] + $color['offset']) / 2;
                $wavyColors[] = [
                    'offset' => $midOffset,
                    'color' => $color['color'],
                ];
            }
            $wavyColors[] = $color;
        }

        return $this->createLinearGradient($id, $wavyColors, 0, 0, 100, 100);
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function generateMarbleAvatar(string $seed, Name $nameObj, int $size, array $options): string
    {
        // Generate unique IDs based on seed
        $maskId = 'mask-' . substr(md5($seed), 0, 8);
        $filterId = 'filter-' . substr(md5($seed), 0, 8);

        // Generate colors from seed
        $colors = $this->generateMarbleColors($nameObj);

        // Generate transform parameters from seed
        $hash = md5($seed);
        $transforms = [
            'translate1' => [
                'x' => (hexdec(substr($hash, 0, 2)) % 15) - 7,
                'y' => (hexdec(substr($hash, 2, 2)) % 15) - 7,
            ],
            'rotate1' => (hexdec(substr($hash, 4, 3)) % 360),
            'scale1' => 1.2 + ((hexdec(substr($hash, 7, 2)) % 30) / 100),
            'translate2' => [
                'x' => (hexdec(substr($hash, 9, 2)) % 15) - 7,
                'y' => (hexdec(substr($hash, 11, 2)) % 15) - 7,
            ],
            'rotate2' => (hexdec(substr($hash, 13, 3)) % 360),
            'scale2' => 1.2 + ((hexdec(substr($hash, 16, 2)) % 30) / 100),
        ];

        // Define the abstract shapes (scaled to viewBox 0 0 80 80)
        $shape1 = 'M32.414 59.35L50.376 70.5H72.5v-71H33.728L26.5 13.381l19.057 27.08L32.414 59.35z';
        $shape2 = 'M22.216 24L0 46.75l14.108 38.129L78 86l-3.081-59.276-22.378 4.005 12.972 20.186-23.35 27.395L22.215 24z';

        // Build SVG
        $svg = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 80 80" fill="none" role="img">';

        // Define mask based on shape option
        $shape = is_string($options['shape'] ?? null) ? $options['shape'] : 'circle';
        $maskRx = match (strtolower($shape)) {
            'square' => '0',
            'hexagon' => '8',
            default => '160', // circle
        };

        $svg .= '<mask id="' . htmlspecialchars($maskId) . '" maskUnits="userSpaceOnUse" x="0" y="0" width="80" height="80">';
        $svg .= '<rect width="80" height="80" rx="' . $maskRx . '" fill="#FFFFFF"/>';
        $svg .= '</mask>';

        // Masked content
        $svg .= '<g mask="url(#' . htmlspecialchars($maskId) . ')">';

        // Background rectangle
        $svg .= '<rect width="80" height="80" fill="' . htmlspecialchars((string) $colors[0]) . '"/>';

        // First shape layer
        $svg .= '<path filter="url(#' . htmlspecialchars($filterId) . ')" ';
        $svg .= 'd="' . $shape1 . '" ';
        $svg .= 'fill="' . htmlspecialchars((string) $colors[1]) . '" ';
        $svg .= 'transform="translate(' . $transforms['translate1']['x'] . ' ' . $transforms['translate1']['y'] . ') ';
        $svg .= 'rotate(' . $transforms['rotate1'] . ' 40 40) ';
        $svg .= 'scale(' . number_format($transforms['scale1'], 1) . ')"/>';

        // Second shape layer with overlay blend mode
        $svg .= '<path filter="url(#' . htmlspecialchars($filterId) . ')" ';
        $svg .= 'style="mix-blend-mode: overlay;" ';
        $svg .= 'd="' . $shape2 . '" ';
        $svg .= 'fill="' . htmlspecialchars((string) $colors[2]) . '" ';
        $svg .= 'transform="translate(' . $transforms['translate2']['x'] . ' ' . $transforms['translate2']['y'] . ') ';
        $svg .= 'rotate(' . $transforms['rotate2'] . ' 40 40) ';
        $svg .= 'scale(' . number_format($transforms['scale2'], 1) . ')"/>';

        $svg .= '</g>';

        // Define filter (Gaussian blur)
        $blurAmount = is_int($options['marbleBlur'] ?? null) ? $options['marbleBlur'] : 7;
        $svg .= '<defs>';
        $svg .= '<filter id="' . htmlspecialchars($filterId) . '" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">';
        $svg .= '<feFlood flood-opacity="0" result="BackgroundImageFix"/>';
        $svg .= '<feBlend in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>';
        $svg .= '<feGaussianBlur stdDeviation="' . $blurAmount . '" result="effect1_foregroundBlur"/>';
        $svg .= '</filter>';
        $svg .= '</defs>';

        return $svg . '</svg>';
    }

    /**
     * @return array<string>
     */
    protected function generateMarbleColors(Name $name): array
    {
        // Get base color from name
        $baseColor = $name->getHexColor()->toHSL();

        // Generate 3 colors: background + 2 shapes
        $colors = [];

        // Color 1: Base color
        $colors[] = $baseColor->toHex()->__toString();

        // Color 2: Shift hue by 60-120 degrees
        $color2 = clone $baseColor;
        $color2->setHue(($baseColor->getHue() + 90) % 360);
        $color2->setLightness(0.5);
        $color2->setSaturation(0.7);
        $colors[] = $color2->toHex()->__toString();

        // Color 3: Shift hue in opposite direction
        $color3 = clone $baseColor;
        $color3->setHue(($baseColor->getHue() + 180) % 360);
        $color3->setLightness(0.6);
        $color3->setSaturation(0.75);
        $colors[] = $color3->toHex()->__toString();

        return $colors;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function createShapeSvg(int $size, string $shape, string $gradientId, array $options): string
    {
        $rotation = is_int($options['rotation'] ?? null) ? $options['rotation'] : 0;

        return match (strtolower($shape)) {
            'square' => $this->createSquareSvg($size, $gradientId),
            'hexagon' => $this->createHexagonSvg($size, $gradientId, $rotation),
            default => $this->createCircleSvg($size, $gradientId),
        };
    }

    protected function createCircleSvg(int $size, string $gradientId): string
    {
        $radius = $size / 2;
        return '<circle cx="' . $radius . '" cy="' . $radius . '" r="' . $radius . '" fill="url(#' . htmlspecialchars($gradientId) . ')"/>';
    }

    protected function createSquareSvg(int $size, string $gradientId): string
    {
        return '<rect x="0" y="0" width="' . $size . '" height="' . $size . '" fill="url(#' . htmlspecialchars($gradientId) . ')"/>';
    }

    protected function createHexagonSvg(int $size, string $gradientId, int $rotation): string
    {
        $rotationRad = deg2rad($rotation);
        $points = [];

        for ($i = 0; $i <= 5; $i++) {
            $angle = pi() / 3 * $i + $rotationRad;
            $x = $size / 2 * cos($angle) + $size / 2;
            $y = $size / 2 * sin($angle) + $size / 2;
            $points[] = $x . ',' . $y;
        }

        return '<polygon points="' . implode(' ', $points) . '" fill="url(#' . htmlspecialchars($gradientId) . ')"/>';
    }
}
