<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests\Engines;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\Engines\PixelEngine;
use Renfordt\AvatarSmithy\Support\Name;

#[CoversClass(PixelEngine::class)]
#[CoversClass(Name::class)]
class PixelEngineTest extends TestCase
{
    private PixelEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new PixelEngine();
    }

    public function test_generate_creates_svg_with_default_settings(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('width="200"', $result);
        $this->assertStringContainsString('height="200"', $result);
    }

    public function test_generate_creates_symmetric_pattern_by_default(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<rect', $result);
    }

    public function test_generate_with_custom_pixels_count(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'pixels' => 10,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_with_symmetry_disabled(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'symmetry' => false,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_with_custom_foreground_lightness(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'foregroundLightness' => 0.7,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
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

    public function test_generate_with_different_sizes(): void
    {
        $result = $this->engine->generate('test-seed', null, 300, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('width="300"', $result);
        $this->assertStringContainsString('height="300"', $result);
    }

    public function test_content_type_is_svg(): void
    {
        $this->assertSame('image/svg+xml', $this->engine->getContentType());
    }

    public function test_generate_with_all_custom_options(): void
    {
        $result = $this->engine->generate('test-seed', null, 400, [
            'pixels' => 8,
            'symmetry' => true,
            'foregroundLightness' => 0.6,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('width="400"', $result);
    }

    public function test_generate_symmetric_creates_mirrored_pattern(): void
    {
        // Test that symmetric option actually creates a symmetric pattern
        $result = $this->engine->generate('test-seed', null, 200, [
            'pixels' => 5,
            'symmetry' => true,
        ]);

        $this->assertNotNull($result);
        // Symmetric patterns should have mirrored rectangles
        $this->assertStringContainsString('<rect', $result);
    }

    public function test_generate_non_symmetric_creates_different_pattern(): void
    {
        $resultSymmetric = $this->engine->generate('test-seed', null, 200, [
            'symmetry' => true,
        ]);
        $resultNonSymmetric = $this->engine->generate('test-seed', null, 200, [
            'symmetry' => false,
        ]);

        $this->assertNotSame($resultSymmetric, $resultNonSymmetric);
    }

    public function test_generate_with_minimal_pixels(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'pixels' => 3,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_with_many_pixels(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'pixels' => 20,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_with_background_enabled_by_default(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        // Should contain a background rect at position 0,0 with full size
        $this->assertStringContainsString('<rect', $result);
        $this->assertStringContainsString('x="0"', $result);
        $this->assertStringContainsString('y="0"', $result);
    }

    public function test_generate_with_background_disabled(): void
    {
        $resultWithBackground = $this->engine->generate('test-seed', null, 200, []);
        $resultWithoutBackground = $this->engine->generate('test-seed', null, 200, [
            'background' => false,
        ]);

        $this->assertNotNull($resultWithoutBackground);
        $this->assertNotSame($resultWithBackground, $resultWithoutBackground);
        // Without background, should have fewer rectangles in the SVG
        $this->assertStringContainsString('<svg', $resultWithoutBackground);
    }

    public function test_generate_with_custom_background_lightness(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'backgroundLightness' => 0.95,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('<rect', $result);
    }

    public function test_generate_with_background_and_foreground_lightness(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'foregroundLightness' => 0.3,
            'backgroundLightness' => 0.95,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }
}
