<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

use Renfordt\AvatarSmithy\Support\Name;
use Renfordt\Colors\HSLColor;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGPolygon;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Texts\SVGText;
use SVG\SVG;

use function clamp;

class InitialsEngine extends AbstractEngine
{
    /**
     * @param array<string, mixed> $options
     */
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        $nameObj = Name::make($name ?? $seed);

        if (in_array($nameObj->getInitials(), ['', '0'], true)) {
            return null;
        }

        $shape = is_string($options['shape'] ?? null) ? $options['shape'] : 'circle';
        $foregroundLightness = is_float($options['foregroundLightness'] ?? null) ? (float) clamp($options['foregroundLightness'], 0, 1) : 0.35;
        $backgroundLightness = is_float($options['backgroundLightness'] ?? null) ? (float) clamp($options['backgroundLightness'], 0, 1) : 0.8;
        $fontSize = is_int($options['fontSize'] ?? null) ? $options['fontSize'] : $this->calculateFontSize($size, $nameObj->getInitials());
        $fontWeight = is_string($options['fontWeight'] ?? null) ? $options['fontWeight'] : 'normal';
        $fontFamily = is_string($options['fontFamily'] ?? null) ? $options['fontFamily'] : 'Segoe UI, Helvetica, sans-serif';

        [$darkColor, $lightColor] = $this->getColorSet($nameObj, $foregroundLightness, $backgroundLightness);

        $svg = new SVG($size, $size);
        $doc = $svg->getDocument();

        $rotation = is_int($options['rotation'] ?? null) ? $options['rotation'] : 0;
        $shapeElement = match (strtolower($shape)) {
            'square' => $this->createSquare($size, $lightColor),
            'hexagon' => $this->createHexagon($size, $lightColor, $rotation),
            default => $this->createCircle($size, $lightColor),
        };

        $textElement = $this->createText($nameObj->getInitials(), $darkColor, $fontSize, $fontWeight, $fontFamily);

        $doc->addChild($shapeElement);
        $doc->addChild($textElement);

        return $svg->__toString();
    }

    /**
     * @return array{HSLColor, HSLColor}
     */
    protected function getColorSet(Name $name, float $foregroundLightness, float $backgroundLightness): array
    {
        $color = $name->getHexColor();

        $dark = $color->toHSL();
        $dark->setLightness($foregroundLightness);

        $light = $color->toHSL();
        $light->setLightness($backgroundLightness);

        return [$dark, $light];
    }

    protected function createCircle(int $size, HSLColor $color): SVGCircle
    {
        $radius = $size / 2;
        $circle = new SVGCircle($radius, $radius, $radius);
        $circle->setStyle('fill', $color->toHex());
        return $circle;
    }

    protected function createSquare(int $size, HSLColor $color): SVGRect
    {
        $rect = new SVGRect(0, 0, $size, $size);
        $rect->setStyle('fill', $color->toHex());
        return $rect;
    }

    protected function createHexagon(int $size, HSLColor $color, int $rotation): SVGPolygon
    {
        $rotationRad = deg2rad($rotation);
        $points = [];

        for ($i = 0; $i <= 5; $i++) {
            $angle = pi() / 3 * $i + $rotationRad;
            $x = $size / 2 * cos($angle) + $size / 2;
            $y = $size / 2 * sin($angle) + $size / 2;
            $points[] = [$x, $y];
        }

        $polygon = new SVGPolygon($points);
        $polygon->setStyle('fill', $color->toHex());
        return $polygon;
    }

    protected function createText(string $text, HSLColor $color, int $fontSize, string $fontWeight, string $fontFamily): SVGText
    {
        $textElement = new SVGText($text, '50%', '55%');
        $textElement->setStyle('fill', $color->toHex());
        $textElement->setStyle('text-anchor', 'middle');
        $textElement->setStyle('dominant-baseline', 'middle');
        $textElement->setStyle('font-weight', $fontWeight);
        $textElement->setFontFamily($fontFamily);
        $textElement->setFontSize($fontSize . 'px');
        return $textElement;
    }

    protected function calculateFontSize(int $size, string $initials): int
    {
        return intval($size * (0.5 - sin(0.5 * strlen($initials) - 1) / 5));
    }
}
