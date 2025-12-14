<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

use Renfordt\AvatarSmithy\Engines\Traits\PixelMatrixTrait;
use Renfordt\AvatarSmithy\Support\Name;
use SVG\Nodes\Shapes\SVGRect;
use SVG\SVG;

class MultiColorPixelEngine extends AbstractEngine
{
    use PixelMatrixTrait;
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
            foreach ($matrix as $y => $array) {
                foreach ($array as $x => $value) {
                    if ($value) {
                        $rect = new SVGRect(
                            (int) ($x * $pixelSize),
                            (int) ($y * $pixelSize),
                            $pixelSize,
                            $pixelSize
                        );
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

            $factor = $i / max(1, $numColors - 1);

            $hueShift = ($factor * 60) - 30;
            $hsl->hue = (int) ($baseColor->hue + $hueShift);

            $hsl->lightness = 0.35 + ($factor * 0.35);

            $hsl->saturation = 0.6 + (sin($factor * pi()) * 0.2);

            $colors[] = $hsl;
        }

        return $colors;
    }

    protected function getColorIndexForPosition(int $x, int $y, string $hash, int $numColors): int
    {
        $position = $x * 100 + $y;
        $hashValue = hexdec(substr(md5($hash . $position), 0, 8));
        return $hashValue % $numColors;
    }
}
