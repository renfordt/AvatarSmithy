<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests\Engines;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\Engines\InitialsEngine;
use Renfordt\AvatarSmithy\Support\Name;

#[CoversClass(InitialsEngine::class)]
#[CoversClass(Name::class)]
class InitialsEngineTest extends TestCase
{
    private InitialsEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new InitialsEngine();
    }

    public function test_generate_creates_svg_with_circle_shape(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Doe', 200, ['shape' => 'circle']);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('<circle', $result);
        $this->assertStringContainsString('JD', $result);
    }

    public function test_generate_creates_svg_with_square_shape(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Doe', 200, ['shape' => 'square']);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('<rect', $result);
        $this->assertStringContainsString('JD', $result);
    }

    public function test_generate_creates_svg_with_hexagon_shape(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Doe', 200, ['shape' => 'hexagon']);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('<polygon', $result);
        $this->assertStringContainsString('JD', $result);
    }

    public function test_generate_with_hexagon_and_rotation(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Doe', 200, [
            'shape' => 'hexagon',
            'rotation' => 45,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<polygon', $result);
    }

    public function test_generate_uses_default_circle_for_unknown_shape(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Doe', 200, ['shape' => 'unknown']);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<circle', $result);
    }

    public function test_generate_returns_null_for_empty_initials(): void
    {
        $result = $this->engine->generate('', '', 200, []);

        $this->assertNull($result);
    }

    public function test_generate_returns_null_for_zero_initials(): void
    {
        $result = $this->engine->generate('0', '0', 200, []);

        $this->assertNull($result);
    }

    public function test_generate_with_custom_foreground_lightness(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Doe', 200, [
            'foregroundLightness' => 0.5,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_with_custom_background_lightness(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Doe', 200, [
            'backgroundLightness' => 0.9,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_with_custom_font_size(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Doe', 200, [
            'fontSize' => 50,
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('font-size: 50px', $result);
    }

    public function test_generate_with_custom_font_weight(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Doe', 200, [
            'fontWeight' => 'bold',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('font-weight: bold', $result);
    }

    public function test_generate_with_custom_font_family(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Doe', 200, [
            'fontFamily' => 'Arial, sans-serif',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('Arial, sans-serif', $result);
    }

    public function test_generate_uses_seed_when_name_is_null(): void
    {
        $result = $this->engine->generate('test@example.com', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_generate_with_single_letter_name(): void
    {
        $result = $this->engine->generate('test@example.com', 'X', 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('X', $result);
    }

    public function test_generate_with_three_word_name(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Michael Doe', 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('JMD', $result);
    }

    public function test_generate_with_unicode_name(): void
    {
        $result = $this->engine->generate('test@example.com', 'Jörg Müller', 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<svg', $result);
    }

    public function test_content_type_is_svg(): void
    {
        $this->assertSame('image/svg+xml', $this->engine->getContentType());
    }

    public function test_generate_creates_deterministic_output(): void
    {
        $result1 = $this->engine->generate('test@example.com', 'John Doe', 200, []);
        $result2 = $this->engine->generate('test@example.com', 'John Doe', 200, []);

        $this->assertSame($result1, $result2);
    }

    public function test_generate_creates_different_output_for_different_names(): void
    {
        $result1 = $this->engine->generate('test@example.com', 'John Doe', 200, []);
        $result2 = $this->engine->generate('test@example.com', 'Jane Smith', 200, []);

        $this->assertNotSame($result1, $result2);
    }

    public function test_generate_respects_size_parameter(): void
    {
        $result = $this->engine->generate('test@example.com', 'John Doe', 300, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('width="300"', $result);
        $this->assertStringContainsString('height="300"', $result);
    }
}
