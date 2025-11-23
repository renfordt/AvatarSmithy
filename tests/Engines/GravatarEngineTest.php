<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests\Engines;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\Engines\GravatarEngine;

#[CoversClass(GravatarEngine::class)]
class GravatarEngineTest extends TestCase
{
    private GravatarEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new GravatarEngine();
    }

    public function test_generate_returns_gravatar_url_with_defaults(): void
    {
        $result = $this->engine->generate('test@example.com', null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringStartsWith('https://www.gravatar.com/avatar/', $result);
        $this->assertStringContainsString('d=mp', $result);
        $this->assertStringContainsString('r=g', $result);
        $this->assertStringContainsString('s=200', $result);
        $this->assertStringContainsString('f=y', $result);
    }

    public function test_generate_uses_md5_hash_of_email(): void
    {
        $email = 'test@example.com';
        $expectedHash = md5(strtolower(trim($email)));
        $result = $this->engine->generate($email, null, 200, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString($expectedHash, $result);
    }

    public function test_generate_with_custom_default_option(): void
    {
        $result = $this->engine->generate('test@example.com', null, 200, [
            'default' => 'identicon',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('d=identicon', $result);
    }

    public function test_generate_with_custom_rating_option(): void
    {
        $result = $this->engine->generate('test@example.com', null, 200, [
            'rating' => 'pg',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('r=pg', $result);
    }

    public function test_generate_with_custom_size(): void
    {
        $result = $this->engine->generate('test@example.com', null, 512, []);

        $this->assertNotNull($result);
        $this->assertStringContainsString('s=512', $result);
    }

    public function test_generate_with_all_custom_options(): void
    {
        $result = $this->engine->generate('test@example.com', null, 300, [
            'default' => 'robohash',
            'rating' => 'x',
        ]);

        $this->assertNotNull($result);
        $this->assertStringContainsString('d=robohash', $result);
        $this->assertStringContainsString('r=x', $result);
        $this->assertStringContainsString('s=300', $result);
    }

    public function test_generate_trims_and_lowercases_email(): void
    {
        $result1 = $this->engine->generate('  TEST@EXAMPLE.COM  ', null, 200, []);
        $result2 = $this->engine->generate('test@example.com', null, 200, []);

        $this->assertNotNull($result1);
        $this->assertNotNull($result2);

        // Both should have the same hash
        $hash = md5('test@example.com');
        $this->assertStringContainsString($hash, $result1);
        $this->assertStringContainsString($hash, $result2);
    }

    public function test_content_type_is_text_html(): void
    {
        $this->assertSame('text/html', $this->engine->getContentType());
    }

    public function test_generate_is_deterministic(): void
    {
        $result1 = $this->engine->generate('test@example.com', null, 200, []);
        $result2 = $this->engine->generate('test@example.com', null, 200, []);

        $this->assertSame($result1, $result2);
    }

    public function test_generate_different_emails_produce_different_hashes(): void
    {
        $result1 = $this->engine->generate('test1@example.com', null, 200, []);
        $result2 = $this->engine->generate('test2@example.com', null, 200, []);

        $this->assertNotSame($result1, $result2);
    }

    public function test_generate_ignores_name_parameter(): void
    {
        $result1 = $this->engine->generate('test@example.com', 'John Doe', 200, []);
        $result2 = $this->engine->generate('test@example.com', 'Jane Smith', 200, []);

        $this->assertSame($result1, $result2);
    }
}
