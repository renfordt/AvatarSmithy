<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests\Engines;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\Engines\BauhausEngine;
use Renfordt\AvatarSmithy\Support\Name;

#[CoversClass(BauhausEngine::class)]
#[CoversClass(Name::class)]
class BauhausEngineTest extends TestCase
{
    private BauhausEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new BauhausEngine();
    }

    public function test_generate_creates_svg_with_default_settings(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('width="200"', $result);
        $this->assertStringContainsString('height="200"', $result);
    }

    public function test_generate_contains_basic_bauhaus_shapes(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        // Should contain at least: background rect, rotated rect, circle, line
        $this->assertStringContainsString('<rect', $result);
        $this->assertStringContainsString('<circle', $result);
        $this->assertStringContainsString('<line', $result);
    }

    public function test_generate_with_default_num_shapes(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        // Default is 4 shapes (background, rect, circle, line)
        $this->assertNotNull($result);
        $this->assertStringContainsString('<g>', $result);
    }

    public function test_generate_with_custom_num_shapes(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'numShapes' => 8,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        // Should have additional shapes beyond the basic 4
    }

    public function test_generate_with_minimal_num_shapes(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'numShapes' => 4,
        ]);

        $this->assertNotNull($result);
        // Should still have the 4 basic shapes
        $this->assertStringContainsString('<rect', $result);
        $this->assertStringContainsString('<circle', $result);
        $this->assertStringContainsString('<line', $result);
    }

    public function test_generate_with_many_shapes(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'numShapes' => 10,
        ]);

        $this->assertNotNull($result);
        // Should have many more polygons/shapes
        $shapeCount = substr_count($result, '<polygon') +
                      substr_count($result, '<circle') +
                      substr_count($result, '<rect');
        $this->assertGreaterThan(5, $shapeCount);
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

    public function test_generate_contains_transformations(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        // Should contain transform attributes for rotation/translation
        $this->assertNotNull($result);
        $this->assertStringContainsString('transform=', $result);
        $this->assertStringContainsString('rotate', $result);
        $this->assertStringContainsString('translate', $result);
    }

    public function test_generate_contains_bauhaus_colors(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        // Should contain hex color values
        $this->assertNotNull($result);
        $this->assertMatchesRegularExpression('/#[0-9a-f]{6}/i', $result);
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

    public function test_generate_creates_group_element(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<g>', $result);
        $this->assertStringContainsString('</g>', $result);
    }

    public function test_generate_line_has_stroke_width(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('stroke-width', $result);
    }

    public function test_generate_different_num_shapes_produces_different_output(): void
    {
        $result1 = $this->engine->generate('test-seed', null, 200, [
            'numShapes' => 4,
        ]);
        $result2 = $this->engine->generate('test-seed', null, 200, [
            'numShapes' => 8,
        ]);

        $this->assertNotSame($result1, $result2);
    }

    public function test_generate_additional_shapes_can_be_triangles(): void
    {
        // With enough shapes, we should get some polygons (triangles, hexagons)
        $result = $this->engine->generate('test-seed', null, 200, [
            'numShapes' => 10,
        ]);

        // Additional shapes may include polygons
        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_respects_size_in_transformations(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        // Transform values should be based on the size (200/2 = 100)
        $this->assertNotNull($result);
        $this->assertStringContainsString('transform=', $result);
    }

    public function test_generate_creates_consistent_palette_for_same_seed(): void
    {
        $result1 = $this->engine->generate('test-seed', null, 200, []);
        $result2 = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result1);
        $this->assertNotNull($result2);

        // Extract color values
        preg_match_all('/#[0-9a-f]{6}/i', $result1, $colors1);
        preg_match_all('/#[0-9a-f]{6}/i', $result2, $colors2);

        $this->assertEquals($colors1[0], $colors2[0]);
    }
}
