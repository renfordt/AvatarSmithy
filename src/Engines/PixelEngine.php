<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

use Renfordt\AvatarSmithy\Engines\Traits\PixelMatrixTrait;
use Renfordt\AvatarSmithy\Support\Name;
use Renfordt\Colors\HSLColor;
use SVG\Nodes\Shapes\SVGRect;
use SVG\SVG;

use function clamp;

class PixelEngine extends AbstractEngine
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
        $hsl->lightness = $lightness;

        return $hsl;
    }
}
