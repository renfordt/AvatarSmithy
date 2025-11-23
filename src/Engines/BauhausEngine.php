<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

use Renfordt\AvatarSmithy\Support\Name;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGLine;
use SVG\Nodes\Shapes\SVGPolygon;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGGroup;
use SVG\SVG;

class BauhausEngine extends AbstractEngine
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        $nameObj = Name::make($seed);
        $hash = $nameObj->getHash();
        $numShapes = is_int($options['numShapes'] ?? null) ? $options['numShapes'] : 4;

        $colors = $this->generateColorPalette($hash);

        $svg = new SVG($size, $size);
        $doc = $svg->getDocument();

        $group = new SVGGroup();

        $background = new SVGRect(0, 0, $size, $size);
        $background->setStyle('fill', $colors[0]);
        $group->addChild($background);

        $rectWidthMultiplier = $this->getValueFromHash($hash, 0) < 0.3 ?
            $this->getValueFromHash($hash, 9, 0.6, 0.9) : 1.0;
        $rectWidth = $size * $rectWidthMultiplier;

        $rectHeightType = $this->getValueFromHash($hash, 1);
        if ($rectHeightType < 0.4) {
            // Thin bar
            $rectHeight = $size * $this->getValueFromHash($hash, 10, 0.1, 0.15);
        } elseif ($rectHeightType < 0.7) {
            // Medium rectangle
            $rectHeight = $size * $this->getValueFromHash($hash, 11, 0.3, 0.6);
        } else {
            // Full height
            $rectHeight = $size;
        }

        $rect = new SVGRect($size * 0.125, $size * 0.375, $rectWidth, $rectHeight);
        $rect->setStyle('fill', $colors[1]);
        $translateX = $this->getValueFromHash($hash, 2, -$size * 0.225, $size * 0.2);
        $translateY = $this->getValueFromHash($hash, 3, -$size * 0.225, $size * 0.2);
        $rotateAngle = $this->getValueFromHash($hash, 4, 0, 360);
        $rect->setAttribute('transform', sprintf(
            'translate(%.1f %.1f) rotate(%.0f %d %d)',
            $translateX,
            $translateY,
            $rotateAngle,
            $size / 2,
            $size / 2
        ));
        $group->addChild($rect);

        $circleRadius = $size * $this->getValueFromHash($hash, 13, 0.15, 0.25);
        $circle = new SVGCircle($size / 2, $size / 2, $circleRadius);
        $circle->setStyle('fill', $colors[2]);
        $circleTranslateX = $this->getValueFromHash($hash, 5, -$size * 0.225, $size * 0.225);
        $circleTranslateY = $this->getValueFromHash($hash, 6, -$size * 0.225, $size * 0.225);
        $circle->setAttribute('transform', sprintf(
            'translate(%.1f %.1f)',
            $circleTranslateX,
            $circleTranslateY
        ));
        $group->addChild($circle);

        $line = new SVGLine(0, $size / 2, $size, $size / 2);
        $line->setStyle('stroke', $colors[3]);
        $line->setStyle('stroke-width', (string) ($size * 0.025));
        $lineTranslateX = $this->getValueFromHash($hash, 7, -$size * 0.2, $size * 0.2);
        $lineTranslateY = $this->getValueFromHash($hash, 8, -$size * 0.2, $size * 0.2);
        $lineRotateAngle = $this->getValueFromHash($hash, 12, 0, 360);
        $line->setAttribute('transform', sprintf(
            'translate(%.1f %.1f) rotate(%.0f %d %d)',
            $lineTranslateX,
            $lineTranslateY,
            $lineRotateAngle,
            $size / 2,
            $size / 2
        ));
        $group->addChild($line);

        if ($numShapes > 4) {
            $additionalShapes = $numShapes - 4;
            for ($i = 0; $i < $additionalShapes; $i++) {
                $shapeIndex = 14 + ($i * 6); // Each shape uses 6 hash indices
                $shape = $this->createAdditionalShape($hash, $shapeIndex, $size, $colors, $i);
                $group->addChild($shape);
            }
        }

        $doc->addChild($group);

        return $svg->__toString();
    }

    /**
     * @return array<string>
     */
    protected function generateColorPalette(string $hash): array
    {
        $palettes = [
            ['#ffe3b3', '#ff9a52', '#ff5252', '#c91e5a', '#3d2922'], // Warm oranges/reds
            ['#c91e5a', '#3d2922', '#ffe3b3', '#ff9a52', '#ff5252'], // Dark with warm accents
            ['#ff5252', '#c91e5a', '#3d2922', '#ffe3b3', '#ff9a52'], // Red-dominant
            ['#ff9a52', '#ff5252', '#c91e5a', '#3d2922', '#ffe3b3'], // Orange-dominant
        ];

        $paletteIndex = hexdec(substr($hash, 0, 2)) % count($palettes);

        return $palettes[$paletteIndex];
    }

    /**
     * @param  array<string>  $colors
     */
    protected function createAdditionalShape(string $hash, int $baseIndex, int $size, array $colors, int $shapeNumber): SVGPolygon|SVGCircle|SVGRect
    {
        $shapeType = $this->getValueFromHash($hash, $baseIndex);
        $colorIndex = (1 + $shapeNumber) % count($colors);
        $color = $colors[$colorIndex];
        if ($shapeType < 0.25) {
            return $this->createTriangle($hash, $baseIndex, $size, $color);
        }
        if ($shapeType < 0.5) {
            return $this->createHexagon($hash, $baseIndex, $size, $color);
        }

        if ($shapeType < 0.75) {
            return $this->createSmallCircle($hash, $baseIndex, $size, $color);
        }

        return $this->createSmallRectangle($hash, $baseIndex, $size, $color);
    }

    protected function createTriangle(string $hash, int $baseIndex, int $size, string $color): SVGPolygon
    {
        $triangleSize = $size * $this->getValueFromHash($hash, $baseIndex + 1, 0.2, 0.4);
        $centerX = $size * $this->getValueFromHash($hash, $baseIndex + 2, 0.15, 0.85);
        $centerY = $size * $this->getValueFromHash($hash, $baseIndex + 3, 0.15, 0.85);

        $points = [
            [$centerX, $centerY - $triangleSize / 2],
            [$centerX - $triangleSize / 2, $centerY + $triangleSize / 2],
            [$centerX + $triangleSize / 2, $centerY + $triangleSize / 2],
        ];

        $triangle = new SVGPolygon($points);
        $triangle->setStyle('fill', $color);

        $rotateAngle = $this->getValueFromHash($hash, $baseIndex + 4, 0, 360);
        $triangle->setAttribute('transform', sprintf(
            'rotate(%.0f %.1f %.1f)',
            $rotateAngle,
            $centerX,
            $centerY
        ));

        return $triangle;
    }

    protected function createHexagon(string $hash, int $baseIndex, int $size, string $color): SVGPolygon
    {
        $hexSize = $size * $this->getValueFromHash($hash, $baseIndex + 1, 0.15, 0.3);
        $centerX = $size * $this->getValueFromHash($hash, $baseIndex + 2, 0.15, 0.85);
        $centerY = $size * $this->getValueFromHash($hash, $baseIndex + 3, 0.15, 0.85);

        $points = [];
        for ($i = 0; $i < 6; $i++) {
            $angle = ($i * 60) * (M_PI / 180);
            $points[] = [
                $centerX + $hexSize * cos($angle),
                $centerY + $hexSize * sin($angle),
            ];
        }

        $hexagon = new SVGPolygon($points);
        $hexagon->setStyle('fill', $color);

        $rotateAngle = $this->getValueFromHash($hash, $baseIndex + 4, 0, 360);
        $hexagon->setAttribute('transform', sprintf(
            'rotate(%.0f %.1f %.1f)',
            $rotateAngle,
            $centerX,
            $centerY
        ));

        return $hexagon;
    }

    protected function createSmallCircle(string $hash, int $baseIndex, int $size, string $color): SVGCircle
    {
        $radius = $size * $this->getValueFromHash($hash, $baseIndex + 1, 0.08, 0.18);
        $centerX = $size * $this->getValueFromHash($hash, $baseIndex + 2, 0.15, 0.85);
        $centerY = $size * $this->getValueFromHash($hash, $baseIndex + 3, 0.15, 0.85);

        $circle = new SVGCircle($centerX, $centerY, $radius);
        $circle->setStyle('fill', $color);

        return $circle;
    }

    protected function createSmallRectangle(string $hash, int $baseIndex, int $size, string $color): SVGRect
    {
        $width = $size * $this->getValueFromHash($hash, $baseIndex + 1, 0.15, 0.35);
        $height = $size * $this->getValueFromHash($hash, $baseIndex + 5, 0.15, 0.35);
        $x = $size * $this->getValueFromHash($hash, $baseIndex + 2, 0.1, 0.8);
        $y = $size * $this->getValueFromHash($hash, $baseIndex + 3, 0.1, 0.8);

        $rect = new SVGRect($x, $y, $width, $height);
        $rect->setStyle('fill', $color);

        $rotateAngle = $this->getValueFromHash($hash, $baseIndex + 4, 0, 360);
        $centerX = $x + $width / 2;
        $centerY = $y + $height / 2;
        $rect->setAttribute('transform', sprintf(
            'rotate(%.0f %.1f %.1f)',
            $rotateAngle,
            $centerX,
            $centerY
        ));

        return $rect;
    }

    protected function getValueFromHash(string $hash, int $index, float $min = 0, float $max = 1): float
    {
        $offset = ($index * 2) % (strlen($hash) - 2);
        $value = hexdec(substr($hash, $offset, 2));
        $normalized = $value / 255.0;

        return $min + ($normalized * ($max - $min));
    }
}
