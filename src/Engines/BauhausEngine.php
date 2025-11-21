<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

use Renfordt\AvatarSmithy\Support\Name;
use Renfordt\Colors\HSLColor;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGLine;
use SVG\Nodes\Shapes\SVGPolygon;
use SVG\Nodes\Shapes\SVGRect;
use SVG\SVG;

use function Renfordt\Clamp\clamp;

class BauhausEngine extends AbstractEngine
{
    /**
     * @param array<string, mixed> $options
     */
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        $nameObj = Name::make($seed);
        $numShapes = is_int($options['numShapes'] ?? null) ? $options['numShapes'] : 6;
        $numColors = is_int($options['numColors'] ?? null) ? $options['numColors'] : 4;

        $colors = $this->generateColorPalette($nameObj, $numColors);
        $shapes = $this->generateShapes($nameObj, $size, $numShapes, $colors);

        $svg = new SVG($size, $size);
        $doc = $svg->getDocument();

        // Add background
        $background = new SVGRect(0, 0, $size, $size);
        $bgColor = clone $colors[0];
        $bgColor->setLightness(0.95);
        $background->setStyle('fill', $bgColor->toHex());
        $doc->addChild($background);

        // Add shapes
        foreach ($shapes as $shape) {
            $doc->addChild($shape);
        }

        return $svg->__toString();
    }

    /**
     * @return array<HSLColor>
     */
    protected function generateColorPalette(Name $name, int $numColors): array
    {
        $baseColor = $name->getHexColor()->toHSL();
        $colors = [];

        // Generate harmonious Bauhaus-inspired colors with vibrant saturation
        $colorSchemes = [
            [0, 0.75, 0.55],    // Base color - vibrant
            [180, 0.70, 0.50],  // Complementary
            [60, 0.80, 0.60],   // Analogous 1 - bright
            [300, 0.75, 0.55],  // Analogous 2
            [120, 0.65, 0.45],  // Triadic
            [240, 0.70, 0.58],  // Triadic 2
        ];

        for ($i = 0; $i < min($numColors, count($colorSchemes)); $i++) {
            $hsl = clone $baseColor;
            $hueShift = $colorSchemes[$i][0];
            $hsl->setHue(($baseColor->getHue() + $hueShift) % 360);
            $hsl->setSaturation(clamp($colorSchemes[$i][1], 0, 1));
            $hsl->setLightness(clamp($colorSchemes[$i][2], 0, 1));
            $colors[] = $hsl;
        }

        // If more colors needed, generate additional vibrant ones
        while (count($colors) < $numColors) {
            $hsl = clone $baseColor;
            $hsl->setHue(($baseColor->getHue() + (count($colors) * 45)) % 360);
            $hsl->setSaturation(clamp(0.70 + (count($colors) % 3) * 0.05, 0, 1));
            $hsl->setLightness(clamp(0.50 + (count($colors) % 4) * 0.08, 0, 1));
            $colors[] = $hsl;
        }

        return $colors;
    }

    /**
     * @param array<HSLColor> $colors
     * @return array<SVGCircle|SVGRect|SVGPolygon|SVGLine>
     */
    protected function generateShapes(Name $name, int $size, int $numShapes, array $colors): array
    {
        $hash = $name->getHash();
        $shapes = [];

        // Use a grid-based approach to ensure better distribution
        $gridSize = ceil(sqrt($numShapes));
        $cellSize = $size / $gridSize;

        for ($i = 0; $i < $numShapes; $i++) {
            // Use hash to determine shape properties
            $shapeType = $this->getShapeType($hash, $i);
            $color = $colors[$i % count($colors)];
            $opacity = clamp(0.7 + ($i % 3) * 0.1, 0, 1);

            // Calculate grid position to ensure distribution
            $gridX = $i % $gridSize;
            $gridY = floor($i / $gridSize);

            // Add random offset within the grid cell for variety
            $offsetX = $this->getValueFromHash($hash, $i * 5, 0, $cellSize * 0.8);
            $offsetY = $this->getValueFromHash($hash, $i * 5 + 1, 0, $cellSize * 0.8);

            $posX = $gridX * $cellSize + $offsetX;
            $posY = $gridY * $cellSize + $offsetY;

            // Much bigger size variation - from very small to very large
            $shapeSize = $this->getValueFromHash($hash, $i * 5 + 2, $size * 0.05, $size * 0.45);

            // Add rotation angle
            $rotation = $this->getValueFromHash($hash, $i * 5 + 3, 0, 360);

            $shape = match ($shapeType) {
                'circle' => $this->createBauhausCircle($posX, $posY, $shapeSize / 2, $color, $opacity, $rotation),
                'square' => $this->createBauhausSquare($posX, $posY, $shapeSize, $color, $opacity, $rotation),
                'triangle' => $this->createBauhausTriangle($posX, $posY, $shapeSize, $color, $opacity, $rotation),
                'rectangle' => $this->createBauhausRectangle($posX, $posY, $shapeSize, $shapeSize * 0.6, $color, $opacity, $rotation),
                'line' => $this->createBauhausLine($posX, $posY, $shapeSize, $color, $opacity, $rotation),
                default => $this->createBauhausCircle($posX, $posY, $shapeSize / 2, $color, $opacity, $rotation),
            };

            $shapes[] = $shape;
        }

        return $shapes;
    }

    protected function getShapeType(string $hash, int $index): string
    {
        $types = ['circle', 'square', 'triangle', 'rectangle', 'line'];
        // Use a better mixing function to ensure varied shape selection
        // Combine multiple hash portions to reduce repetition
        $offset1 = ($index * 7) % (strlen($hash) - 2);
        $offset2 = ($index * 13 + 5) % (strlen($hash) - 2);
        $value = hexdec(substr($hash, $offset1, 2)) ^ hexdec(substr($hash, $offset2, 2));
        return $types[$value % count($types)];
    }

    protected function getValueFromHash(string $hash, int $index, float $min, float $max): float
    {
        // Use prime number offsets to ensure better distribution across different indices
        // XOR multiple hash portions to mix the bits better
        $offset1 = ($index * 11) % (strlen($hash) - 4);
        $offset2 = ($index * 17 + 7) % (strlen($hash) - 4);
        $value1 = hexdec(substr($hash, $offset1, 4));
        $value2 = hexdec(substr($hash, $offset2, 4));
        $value = ($value1 ^ $value2) % 65536; // XOR and wrap to 16-bit range
        $normalized = clamp($value / 65535, 0, 1); // Normalize to 0-1
        return $min + ($normalized * ($max - $min));
    }

    protected function createBauhausCircle(float $cx, float $cy, float $r, HSLColor $color, float $opacity, float $rotation): SVGCircle
    {
        $circle = new SVGCircle($cx, $cy, $r);
        $circle->setStyle('fill', $color->toHex());
        $circle->setStyle('fill-opacity', (string) $opacity);
        $circle->setStyle('stroke', 'none');
        if ($rotation != 0) {
            $circle->setAttribute('transform', "rotate($rotation $cx $cy)");
        }
        return $circle;
    }

    protected function createBauhausSquare(float $x, float $y, float $size, HSLColor $color, float $opacity, float $rotation): SVGRect
    {
        $rect = new SVGRect($x, $y, $size, $size);
        $rect->setStyle('fill', $color->toHex());
        $rect->setStyle('fill-opacity', (string) $opacity);
        $rect->setStyle('stroke', 'none');
        if ($rotation != 0) {
            $centerX = $x + $size / 2;
            $centerY = $y + $size / 2;
            $rect->setAttribute('transform', "rotate($rotation $centerX $centerY)");
        }
        return $rect;
    }

    protected function createBauhausTriangle(float $x, float $y, float $size, HSLColor $color, float $opacity, float $rotation): SVGPolygon
    {
        $points = [
            [$x + $size / 2, $y],                    // Top point
            [$x, $y + $size],                         // Bottom left
            [$x + $size, $y + $size],                // Bottom right
        ];

        $polygon = new SVGPolygon($points);
        $polygon->setStyle('fill', $color->toHex());
        $polygon->setStyle('fill-opacity', (string) $opacity);
        $polygon->setStyle('stroke', 'none');
        if ($rotation != 0) {
            $centerX = $x + $size / 2;
            $centerY = $y + $size * 2 / 3;
            $polygon->setAttribute('transform', "rotate($rotation $centerX $centerY)");
        }
        return $polygon;
    }

    protected function createBauhausRectangle(float $x, float $y, float $width, float $height, HSLColor $color, float $opacity, float $rotation): SVGRect
    {
        $rect = new SVGRect($x, $y, $width, $height);
        $rect->setStyle('fill', $color->toHex());
        $rect->setStyle('fill-opacity', (string) $opacity);
        $rect->setStyle('stroke', 'none');
        if ($rotation != 0) {
            $centerX = $x + $width / 2;
            $centerY = $y + $height / 2;
            $rect->setAttribute('transform', "rotate($rotation $centerX $centerY)");
        }
        return $rect;
    }

    protected function createBauhausLine(float $x, float $y, float $length, HSLColor $color, float $opacity, float $rotation): SVGLine
    {
        // Lines span across the entire image diagonally
        $lineLength = $length * 4; // Make lines much longer
        $x2 = $x + $lineLength;
        $y2 = $y;

        $line = new SVGLine($x, $y, $x2, $y2);
        $line->setStyle('stroke', $color->toHex());
        $line->setStyle('stroke-opacity', (string) $opacity);
        $line->setStyle('stroke-width', (string) ($length * 0.15));
        $line->setStyle('stroke-linecap', 'round');
        if ($rotation != 0) {
            $centerX = $x;
            $centerY = $y;
            $line->setAttribute('transform', "rotate($rotation $centerX $centerY)");
        }
        return $line;
    }
}
