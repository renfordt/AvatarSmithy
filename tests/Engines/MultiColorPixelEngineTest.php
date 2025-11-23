<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests\Engines;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\Engines\MultiColorPixelEngine;
use Renfordt\AvatarSmithy\Support\Name;

#[CoversClass(MultiColorPixelEngine::class)]
#[CoversClass(Name::class)]
class MultiColorPixelEngineTest extends TestCase
{
    private MultiColorPixelEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new MultiColorPixelEngine();
    }

    public function test_generate_creates_svg_with_default_settings(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('width="200"', $result);
        $this->assertStringContainsString('height="200"', $result);
    }

    public function test_generate_with_fill_all_true(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'fillAll' => true,
            'pixels' => 5,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        // With fillAll=true and 5x5 pixels, we should have 25 rectangles
        $this->assertGreaterThan(20, substr_count($result, '<rect'));
    }

    public function test_generate_with_fill_all_false(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'fillAll' => false,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        // With fillAll=false, only some pixels should be filled
        $this->assertGreaterThan(0, substr_count($result, '<rect'));
    }

    public function test_generate_with_custom_num_colors(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'numColors' => 10,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_with_custom_pixels_count(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'pixels' => 8,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_with_symmetry_enabled(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'symmetry' => true,
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

    public function test_generate_with_all_custom_options(): void
    {
        $result = $this->engine->generate('test-seed', null, 300, [
            'pixels' => 7,
            'symmetry' => true,
            'numColors' => 8,
            'fillAll' => true,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('width="300"', $result);
    }

    public function test_content_type_is_svg(): void
    {
        $this->assertSame('image/svg+xml', $this->engine->getContentType());
    }

    public function test_generate_creates_different_output_with_different_num_colors(): void
    {
        $result1 = $this->engine->generate('test-seed', null, 200, [
            'numColors' => 3,
        ]);
        $result2 = $this->engine->generate('test-seed', null, 200, [
            'numColors' => 10,
        ]);

        // Different number of colors should produce different output
        // (due to different color distributions)
        $this->assertNotSame($result1, $result2);
    }

    public function test_generate_fill_all_true_vs_false_produces_different_output(): void
    {
        $resultFillAll = $this->engine->generate('test-seed', null, 200, [
            'fillAll' => true,
        ]);
        $resultNotFillAll = $this->engine->generate('test-seed', null, 200, [
            'fillAll' => false,
        ]);

        $this->assertNotSame($resultFillAll, $resultNotFillAll);
    }

    public function test_generate_with_minimal_colors(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'numColors' => 2,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_with_many_colors(): void
    {
        $result = $this->engine->generate('test-seed', null, 200, [
            'numColors' => 15,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_symmetric_creates_different_pattern_than_non_symmetric(): void
    {
        $resultSymmetric = $this->engine->generate('test-seed', null, 200, [
            'symmetry' => true,
            'fillAll' => false,
        ]);
        $resultNonSymmetric = $this->engine->generate('test-seed', null, 200, [
            'symmetry' => false,
            'fillAll' => false,
        ]);

        $this->assertNotSame($resultSymmetric, $resultNonSymmetric);
    }

    public function test_generate_with_different_sizes(): void
    {
        $result = $this->engine->generate('test-seed', null, 500, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('width="500"', $result);
        $this->assertStringContainsString('height="500"', $result);
    }
}
