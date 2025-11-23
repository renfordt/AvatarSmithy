<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests\Engines;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\Engines\GradientEngine;
use Renfordt\AvatarSmithy\Support\Name;

#[CoversClass(GradientEngine::class)]
#[CoversClass(Name::class)]
class GradientEngineTest extends TestCase
{
    private GradientEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new GradientEngine();
    }

    public function test_generate_creates_svg_with_horizontal_gradient(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'horizontal',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('<linearGradient', $result);
        $this->assertStringContainsString('x1="0%"', $result);
        $this->assertStringContainsString('x2="100%"', $result);
    }

    public function test_generate_creates_svg_with_vertical_gradient(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'vertical',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<linearGradient', $result);
        $this->assertStringContainsString('y1="0%"', $result);
        $this->assertStringContainsString('y2="100%"', $result);
    }

    public function test_generate_creates_svg_with_diagonal_gradient(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'diagonal',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<linearGradient', $result);
        $this->assertStringContainsString('x2="100%"', $result);
        $this->assertStringContainsString('y2="100%"', $result);
    }

    public function test_generate_creates_svg_with_radial_gradient(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'radial',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<radialGradient', $result);
        $this->assertStringContainsString('cx="50%"', $result);
        $this->assertStringContainsString('cy="50%"', $result);
        $this->assertStringContainsString('r="50%"', $result);
    }

    public function test_generate_creates_svg_with_wavy_gradient(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'wavy',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<linearGradient', $result);
        // Wavy gradient should have more color stops
        $this->assertGreaterThan(3, substr_count($result, '<stop'));
    }

    public function test_generate_creates_svg_with_circle_shape(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'shape' => 'circle',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<circle', $result);
    }

    public function test_generate_creates_svg_with_square_shape(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'shape' => 'square',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<rect', $result);
    }

    public function test_generate_creates_svg_with_hexagon_shape(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'shape' => 'hexagon',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<polygon', $result);
    }

    public function test_generate_with_hexagon_and_rotation(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'shape' => 'hexagon',
            'rotation' => 30,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<polygon', $result);
    }

    public function test_generate_with_custom_color_stops(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'colorStops' => 5,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<stop', $result);
        // Should have 5 color stops
        $this->assertEquals(5, substr_count($result, '<stop'));
    }

    public function test_generate_marble_creates_different_svg(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'marble',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        // Marble uses mask and filter
        $this->assertStringContainsString('<mask', $result);
        $this->assertStringContainsString('<filter', $result);
        $this->assertStringContainsString('feGaussianBlur', $result);
    }

    public function test_generate_marble_with_circle_shape(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'marble',
            'shape' => 'circle',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('rx="160"', $result); // circle radius
    }

    public function test_generate_marble_with_square_shape(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'marble',
            'shape' => 'square',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('rx="0"', $result); // square corners
    }

    public function test_generate_marble_with_hexagon_shape(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'marble',
            'shape' => 'hexagon',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('rx="8"', $result); // hexagon radius
    }

    public function test_generate_marble_with_custom_blur(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'marble',
            'marbleBlur' => 10,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('stdDeviation="10"', $result);
    }

    public function test_generate_is_deterministic(): void
    {
        $result1 = $this->engine->generate('test-seed', null, 200, []);
        $result2 = $this->engine->generate('test-seed', null, 200, []);

        $this->assertSame($result1, $result2);
    }

    public function test_generate_different_seeds_produce_different_output(): void
    {
        $result1 = $this->engine->generate('seed1', null, 200, []);
        $result2 = $this->engine->generate('seed2', null, 200, []);

        $this->assertNotSame($result1, $result2);
    }

    public function test_generate_contains_gradient_definition(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<defs>', $result);
        $this->assertStringContainsString('</defs>', $result);
    }

    public function test_generate_gradient_id_is_unique_per_seed(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        // Should contain a gradient ID
        preg_match('/id="gradient-([a-f0-9]+)"/', $result, $matches);
        $this->assertNotEmpty($matches);
        $this->assertEquals(8, strlen($matches[1])); // MD5 substring is 8 chars
    }

    public function test_generate_gradient_id_includes_gradient_type(): void
    {
        $resultHorizontal = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'horizontal',
        ]);
        $resultVertical = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'vertical',
        ]);

        $this->assertNotNull($resultHorizontal);
        $this->assertNotNull($resultVertical);

        // Same seed but different gradient types should have different gradient IDs
        preg_match('/id="gradient-([a-f0-9]+)"/', $resultHorizontal, $matchesH);
        preg_match('/id="gradient-([a-f0-9]+)"/', $resultVertical, $matchesV);

        $this->assertNotEquals($matchesH[1], $matchesV[1]);
    }

    public function test_content_type_is_svg(): void
    {
        $this->assertSame('image/svg+xml', $this->engine->getContentType());
    }

    public function test_generate_with_different_sizes(): void
    {
        $result = $this->engine->generate('test-seed', null, 400, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('width="400"', $result);
        $this->assertStringContainsString('height="400"', $result);
    }

    public function test_generate_uses_default_horizontal_gradient(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<linearGradient', $result);
        // Default horizontal: x1=0%, y1=0%, x2=100%, y2=0%
        $this->assertStringContainsString('x1="0%"', $result);
        $this->assertStringContainsString('x2="100%"', $result);
        $this->assertStringContainsString('y1="0%"', $result);
        $this->assertStringContainsString('y2="0%"', $result);
    }

    public function test_generate_uses_default_circle_shape(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<circle', $result);
    }

    public function test_generate_marble_uses_paths(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'marble',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<path', $result);
        // Should have abstract shape paths
        $this->assertGreaterThanOrEqual(2, substr_count($result, '<path'));
    }

    public function test_generate_marble_has_blend_mode(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'gradientType' => 'marble',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('mix-blend-mode: overlay', $result);
    }

    public function test_generate_with_minimal_color_stops(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'colorStops' => 2,
        ]);

        $this->assertNotNull($result);
        $this->assertEquals(2, substr_count($result, '<stop'));
    }

    public function test_generate_with_many_color_stops(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'colorStops' => 10,
        ]);

        $this->assertNotNull($result);
        $this->assertEquals(10, substr_count($result, '<stop'));
    }

    public function test_generate_color_stops_have_offsets(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'colorStops' => 3,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('offset="0%"', $result);
        $this->assertStringContainsString('offset="100%"', $result);
    }
}
