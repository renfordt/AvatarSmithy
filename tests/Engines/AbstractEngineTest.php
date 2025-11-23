<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests\Engines;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\Engines\AbstractEngine;

#[CoversClass(AbstractEngine::class)]
class AbstractEngineTest extends TestCase
{
    private ConcreteEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new ConcreteEngine();
    }

    public function test_get_content_type_returns_default_svg_xml(): void
    {
        $this->assertSame('image/svg+xml', $this->engine->getContentType());
    }

    public function test_get_content_type_can_be_overridden(): void
    {
        $engine = new ConcreteEngineWithCustomContentType();
        $this->assertSame('image/png', $engine->getContentType());
    }

    public function test_fetch_url_returns_null_for_invalid_url(): void
    {
        $result = $this->engine->testFetchUrl('https://invalid-domain-that-does-not-exist-12345.com/test');

        $this->assertNull($result);
    }

    public function test_fetch_url_returns_null_for_404(): void
    {
        // Using a real domain but non-existent path
        $result = $this->engine->testFetchUrl('https://httpbin.org/status/404');

        $this->assertNull($result);
    }
}

// Concrete implementation for testing AbstractEngine
class ConcreteEngine extends AbstractEngine
{
    /**
     * @param array<string, mixed> $options
     */
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        return 'test-output';
    }

    public function testFetchUrl(string $url): ?string
    {
        return $this->fetchUrl($url);
    }
}

class ConcreteEngineWithCustomContentType extends AbstractEngine
{
    protected string $contentType = 'image/png';

    /**
     * @param array<string, mixed> $options
     */
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        return 'test-output';
    }
}
