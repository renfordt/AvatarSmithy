<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

use Renfordt\AvatarSmithy\Support\Name;
use SVG\Nodes\Shapes\SVGRect;
use SVG\SVG;

use function clamp;

class MultiColorPixelEngine extends AbstractEngine
{
    /**
     * @param array<string, mixed> $options
     */
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        $nameObj = Name::make($seed);

        $pixels = is_int($options['pixels'] ?? null) ? $options['pixels'] : 5;
        $symmetry = is_bool($options['symmetry'] ?? null) ? $options['symmetry'] : true;
        $numColors = is_int($options['numColors'] ?? null) ? $options['numColors'] : 5;
        $fillAll = is_bool($options['fillAll'] ?? null) ? $options['fillAll'] : true;

        $colors = $this->generateColorPalette($nameObj, $numColors);
        $matrix = $symmetry ? $this->generateSymmetricMatrix($nameObj, $pixels) : $this->generateMatrix($nameObj, $pixels);

        $svg = new SVG($size, $size);
        $doc = $svg->getDocument();

        $pixelSize = $size / $pixels;

        if ($fillAll) {
            // Fill all pixels with colors
            for ($y = 0; $y < $pixels; $y++) {
                for ($x = 0; $x < $pixels; $x++) {
                    $rect = new SVGRect(
                        (int) ($x * $pixelSize),
                        (int) ($y * $pixelSize),
                        $pixelSize,
                        $pixelSize
                    );
                    // Use hash to determine color for this pixel position
                    $color = $colors[$this->getColorIndexForPosition($x, $y, $nameObj->getHash(), count($colors))];
                    $rect->setStyle('fill', $color->toHex());
                    $doc->addChild($rect);
                }
            }
        } else {
            // Use pattern from matrix
            foreach ($matrix as $y => $array) {
                foreach ($array as $x => $value) {
                    if ($value) {
                        $rect = new SVGRect(
                            (int) ($x * $pixelSize),
                            (int) ($y * $pixelSize),
                            $pixelSize,
                            $pixelSize
                        );
                        // Use hash to determine color for this pixel position
                        $color = $colors[$this->getColorIndexForPosition($x, $y, $nameObj->getHash(), count($colors))];
                        $rect->setStyle('fill', $color->toHex());
                        $doc->addChild($rect);
                    }
                }
            }
        }

        return $svg->__toString();
    }

    /**
     * @return array<\Renfordt\Colors\HSLColor>
     */
    protected function generateColorPalette(Name $name, int $numColors): array
    {
        $baseColor = $name->getHexColor()->toHSL();
        $colors = [];

        for ($i = 0; $i < $numColors; $i++) {
            $hsl = clone $baseColor;

            // Create harmonious color variations
            $factor = $i / max(1, $numColors - 1);

            // Analogous color scheme - vary hue within 60 degrees
            $hueShift = ($factor * 60) - 30;
            $hsl->setHue((int) ($baseColor->getHue() + $hueShift));

            // Vary lightness to create depth
            $lightness = (float) clamp(0.35 + ($factor * 0.35), 0, 1);
            $hsl->setLightness($lightness);

            // Keep saturation relatively consistent for harmony
            $saturation = (float) clamp(0.6 + (sin($factor * pi()) * 0.2), 0, 1);
            $hsl->setSaturation($saturation);

            $colors[] = $hsl;
        }

        return $colors;
    }

    protected function getColorIndexForPosition(int $x, int $y, string $hash, int $numColors): int
    {
        // Use position and hash to deterministically select a color
        $position = $x * 100 + $y;
        $hashValue = hexdec(substr(md5($hash . $position), 0, 8));
        return $hashValue % $numColors;
    }

    /**
     * @return array<array<bool>>
     */
    protected function generateSymmetricMatrix(Name $name, int $pixels): array
    {
        $hash = $name->getHash();
        $symmetryMatrix = $this->getSymmetryMatrix($pixels);
        $divider = count($symmetryMatrix);
        $matrix = [];

        for ($i = 0; $i < $pixels ** 2; $i++) {
            $index = (int) ($i / 3);
            $data = $this->convertStrToBool(substr($hash, $i, 1));

            foreach ($symmetryMatrix[$i % $divider] as $item) {
                $matrix[$index][$item] = $data;
            }
        }

        return $matrix;
    }

    /**
     * @return array<array<int>>
     */
    protected function getSymmetryMatrix(int $pixels): array
    {
        $items = [];
        $i = $pixels - 1;

        for ($x = 0; $x <= $i / 2; $x++) {
            $items[$x] = [$x];
            if ($x !== $i - $x) {
                $items[$x][] = $i - $x;
            }
        }

        return $items;
    }

    /**
     * @return array<array<bool>>
     */
    protected function generateMatrix(Name $name, int $pixels): array
    {
        $hash = hash('sha256', $name->getHash());
        $matrix = [];

        for ($i = 0; $i < $pixels ** 2; $i++) {
            $matrix[$i % $pixels][(int) floor($i / $pixels)] = $this->convertStrToBool(substr($hash, $i, 1));
        }

        return $matrix;
    }

    protected function convertStrToBool(string $char): bool
    {
        return (bool) round(hexdec($char) / 10);
    }
}
