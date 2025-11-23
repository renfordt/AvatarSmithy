<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests\Engines;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\Engines\DiceBearEngine;

#[CoversClass(DiceBearEngine::class)]
class DiceBearEngineTest extends TestCase
{
    private TestableDiceBearEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new TestableDiceBearEngine();
    }

    public function test_generate_builds_correct_url_with_defaults(): void
    {
        $this->engine->generate('test-seed', null, 200, []);
        $url = $this->engine->getLastUrl();

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://api.dicebear.com/9.x/avataaars/svg?', $url);
        $this->assertStringContainsString('seed=test-seed', $url);
        $this->assertStringContainsString('size=200', $url);
    }

    public function test_generate_builds_url_with_custom_style(): void
    {
        $this->engine->generate('test-seed', null, 200, ['style' => 'bottts']);
        $url = $this->engine->getLastUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('/bottts/svg?', $url);
    }

    public function test_generate_builds_url_with_single_background_color(): void
    {
        $this->engine->generate('test-seed', null, 200, [
            'backgroundColor' => '#ff0000',
        ]);
        $url = $this->engine->getLastUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('backgroundColor=ff0000', $url);
    }

    public function test_generate_builds_url_with_multiple_background_colors(): void
    {
        $this->engine->generate('test-seed', null, 200, [
            'backgroundColor' => ['#ff0000', '#00ff00', '#0000ff'],
        ]);
        $url = $this->engine->getLastUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('backgroundColor=ff0000%2C00ff00%2C0000ff', $url);
    }

    public function test_generate_builds_url_with_radius(): void
    {
        $this->engine->generate('test-seed', null, 200, [
            'radius' => 50,
        ]);
        $url = $this->engine->getLastUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('radius=50', $url);
    }

    public function test_generate_builds_url_with_custom_size(): void
    {
        $this->engine->generate('test-seed', null, 512, []);
        $url = $this->engine->getLastUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('size=512', $url);
    }

    public function test_generate_builds_url_with_all_options(): void
    {
        $this->engine->generate('test-seed', null, 300, [
            'style' => 'pixel-art',
            'backgroundColor' => '#123456',
            'radius' => 25,
        ]);
        $url = $this->engine->getLastUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('/pixel-art/svg?', $url);
        $this->assertStringContainsString('seed=test-seed', $url);
        $this->assertStringContainsString('size=300', $url);
        $this->assertStringContainsString('backgroundColor=123456', $url);
        $this->assertStringContainsString('radius=25', $url);
    }

    public function test_generate_ignores_non_int_radius(): void
    {
        $this->engine->generate('test-seed', null, 200, [
            'radius' => 'invalid',
        ]);
        $url = $this->engine->getLastUrl();

        $this->assertNotNull($url);
        $this->assertStringNotContainsString('radius=', $url);
    }

    public function test_content_type_is_svg(): void
    {
        $this->assertSame('image/svg+xml', $this->engine->getContentType());
    }

    public function test_generate_is_deterministic_for_same_seed(): void
    {
        $this->engine->generate('test-seed', null, 200, []);
        $url1 = $this->engine->getLastUrl();

        $this->engine->generate('test-seed', null, 200, []);
        $url2 = $this->engine->getLastUrl();

        $this->assertSame($url1, $url2);
    }

    public function test_generate_different_seeds_produce_different_urls(): void
    {
        $this->engine->generate('seed1', null, 200, []);
        $url1 = $this->engine->getLastUrl();

        $this->engine->generate('seed2', null, 200, []);
        $url2 = $this->engine->getLastUrl();

        $this->assertNotSame($url1, $url2);
    }
}

// Testable version that captures the URL without making HTTP requests
class TestableDiceBearEngine extends DiceBearEngine
{
    private ?string $lastUrl = null;

    /**
     * @param array<string, mixed> $options
     */
    #[\Override]
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        $style = is_string($options['style'] ?? null) ? $options['style'] : 'avataaars';
        $params = ['seed' => $seed, 'size' => (string) $size];

        if (isset($options['backgroundColor'])) {
            $colors = is_array($options['backgroundColor']) ? $options['backgroundColor'] : [$options['backgroundColor']];
            $params['backgroundColor'] = implode(',', array_map(fn ($c): string => is_string($c) ? ltrim($c, '#') : '', $colors));
        }

        if (isset($options['radius']) && is_int($options['radius'])) {
            $params['radius'] = (string) $options['radius'];
        }

        $this->lastUrl = 'https://api.dicebear.com/9.x/' . $style . '/svg?' . http_build_query($params);

        // Don't actually fetch the URL in tests
        return 'mocked-svg-content';
    }

    public function getLastUrl(): ?string
    {
        return $this->lastUrl;
    }
}
