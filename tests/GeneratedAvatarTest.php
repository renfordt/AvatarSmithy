<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\GeneratedAvatar;

#[CoversClass(GeneratedAvatar::class)]
class GeneratedAvatarTest extends TestCase
{
    public function test_constructor_with_content_only(): void
    {
        $this->expectNotToPerformAssertions();
        $avatar = new GeneratedAvatar('<svg></svg>');
    }

    public function test_constructor_with_content_and_type(): void
    {
        $this->expectNotToPerformAssertions();
        $avatar = new GeneratedAvatar('binary-data', 'image/png');
    }

    public function test_to_string_returns_content(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame($content, (string) $avatar);
    }

    public function test_to_string_method_returns_content(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame($content, $avatar->toString());
    }

    public function test_to_html_with_svg_content(): void
    {
        $content = '<svg xmlns="http://www.w3.org/2000/svg"></svg>';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame($content, $avatar->toHtml());
    }

    public function test_to_html_with_data_uri(): void
    {
        $content = 'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame('<img src="' . $content . '" />', $avatar->toHtml());
    }

    public function test_to_html_with_http_url(): void
    {
        $content = 'http://example.com/avatar.png';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame('<img src="' . $content . '" />', $avatar->toHtml());
    }

    public function test_to_html_with_https_url(): void
    {
        $content = 'https://example.com/avatar.png';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame('<img src="' . $content . '" />', $avatar->toHtml());
    }

    public function test_to_base64_with_svg_content(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content);
        $expected = 'data:image/svg+xml;base64,' . base64_encode($content);

        $this->assertSame($expected, $avatar->toBase64());
    }

    public function test_to_base64_with_custom_content_type(): void
    {
        $content = 'binary-data';
        $avatar = new GeneratedAvatar($content, 'image/png');
        $expected = 'data:image/png;base64,' . base64_encode($content);

        $this->assertSame($expected, $avatar->toBase64());
    }

    public function test_to_base64_with_existing_data_uri(): void
    {
        $content = 'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame($content, $avatar->toBase64());
    }

    public function test_to_url_with_svg_content(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content);
        $expected = 'data:image/svg+xml;base64,' . base64_encode($content);

        $this->assertSame($expected, $avatar->toUrl());
    }

    public function test_to_url_with_http_url(): void
    {
        $content = 'http://example.com/avatar.png';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame($content, $avatar->toUrl());
    }

    public function test_to_url_with_https_url(): void
    {
        $content = 'https://example.com/avatar.png';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame($content, $avatar->toUrl());
    }

    public function test_to_response_without_illuminate_response(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content, 'image/svg+xml');

        ob_start();
        $result = $avatar->toResponse();
        $output = ob_get_clean();

        $this->assertNull($result);
        $this->assertSame($content, $output);
    }

    public function test_stringable_interface(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame($content, (string) $avatar);
    }

    public function test_is_data_uri_detection_with_data_prefix(): void
    {
        $content = 'data:image/png;base64,iVBORw0KGgo=';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame('<img src="' . $content . '" />', $avatar->toHtml());
    }

    public function test_is_url_detection_with_http(): void
    {
        $content = 'http://example.com/avatar.png';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame($content, $avatar->toUrl());
    }

    public function test_is_url_detection_with_https(): void
    {
        $content = 'https://example.com/avatar.png';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame($content, $avatar->toUrl());
    }

    public function test_empty_content(): void
    {
        $avatar = new GeneratedAvatar('');

        $this->assertSame('', $avatar->toString());
    }

    public function test_default_content_type(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content);
        $base64 = $avatar->toBase64();

        $this->assertStringContainsString('image/svg+xml', $base64);
    }

    public function test_custom_content_type_in_base64(): void
    {
        $content = 'image-data';
        $avatar = new GeneratedAvatar($content, 'image/jpeg');
        $base64 = $avatar->toBase64();

        $this->assertStringContainsString('image/jpeg', $base64);
    }
}
