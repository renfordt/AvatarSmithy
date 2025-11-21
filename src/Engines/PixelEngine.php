<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

use Renfordt\AvatarSmithy\Support\Name;
use Renfordt\Colors\HSLColor;
use SVG\Nodes\Shapes\SVGRect;
use SVG\SVG;

use function clamp;

class PixelEngine extends AbstractEngine
{
    /**
     * @param array<string, mixed> $options
     */
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        $nameObj = Name::make($seed);

        $pixels = is_int($options['pixels'] ?? null) ? $options['pixels'] : 5;
        $symmetry = is_bool($options['symmetry'] ?? null) ? $options['symmetry'] : true;
        $foregroundLightness = is_float($options['foregroundLightness'] ?? null) ? (float) clamp($options['foregroundLightness'], 0, 1) : 0.5;

        $color = $this->getColor($nameObj, $foregroundLightness);
        $matrix = $symmetry ? $this->generateSymmetricMatrix($nameObj, $pixels) : $this->generateMatrix($nameObj, $pixels);

        $svg = new SVG($size, $size);
        $doc = $svg->getDocument();

        $pixelSize = $size / $pixels;

        foreach ($matrix as $y => $array) {
            foreach ($array as $x => $value) {
                if ($value) {
                    $rect = new SVGRect(
                        (int) ($x * $pixelSize),
                        (int) ($y * $pixelSize),
                        $pixelSize,
                        $pixelSize
                    );
                    $rect->setStyle('fill', $color->toHex());
                    $doc->addChild($rect);
                }
            }
        }

        return $svg->__toString();
    }

    protected function getColor(Name $name, float $lightness): HSLColor
    {
        $color = $name->getHexColor();
        $hsl = $color->toHSL();
        $hsl->setLightness($lightness);

        return $hsl;
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
