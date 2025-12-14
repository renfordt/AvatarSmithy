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
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame($content, $avatar->toString());
    }

    public function test_constructor_with_content_and_type(): void
    {
        $content = 'binary-data';
        $avatar = new GeneratedAvatar($content, 'image/png');

        $this->assertSame($content, $avatar->toString());
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

        // SVG content is now converted to img tag with data URI for better cross-browser compatibility
        $expected = '<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjwvc3ZnPg==" alt="Avatar" />';
        $this->assertSame($expected, $avatar->toHtml());
    }

    public function test_to_html_with_data_uri(): void
    {
        $content = 'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame('<img src="' . $content . '" alt="Avatar" />', $avatar->toHtml());
    }

    public function test_to_html_with_http_url(): void
    {
        $content = 'http://example.com/avatar.png';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame('<img src="' . $content . '" alt="Avatar" />', $avatar->toHtml());
    }

    public function test_to_html_with_https_url(): void
    {
        $content = 'https://example.com/avatar.png';
        $avatar = new GeneratedAvatar($content);

        $this->assertSame('<img src="' . $content . '" alt="Avatar" />', $avatar->toHtml());
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

        $this->assertSame('<img src="' . $content . '" alt="Avatar" />', $avatar->toHtml());
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

    public function test_save_creates_file(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content);
        $tempFile = sys_get_temp_dir() . '/avatar_test_' . uniqid() . '.svg';

        try {
            $result = $avatar->save($tempFile);

            $this->assertTrue($result);
            $this->assertFileExists($tempFile);
            $this->assertSame($content, file_get_contents($tempFile));
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function test_save_creates_directory_if_not_exists(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content);
        $tempDir = sys_get_temp_dir() . '/avatar_test_dir_' . uniqid();
        $tempFile = $tempDir . '/avatar.svg';

        try {
            $result = $avatar->save($tempFile);

            $this->assertTrue($result);
            $this->assertFileExists($tempFile);
            $this->assertSame($content, file_get_contents($tempFile));
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            if (is_dir($tempDir)) {
                rmdir($tempDir);
            }
        }
    }

    public function test_save_returns_false_on_invalid_path(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content);

        // Create a temporary file, then try to save to a path that treats this file as a directory
        // This should fail on all platforms (Windows, Linux, macOS)
        $tempFile = sys_get_temp_dir() . '/avatar_test_file_' . uniqid() . '.txt';
        file_put_contents($tempFile, 'test');

        try {
            // Try to save to a path where parent is a file, not a directory
            // This will fail because you can't create a directory inside a file
            $result = @$avatar->save($tempFile . '/avatar.svg');

            $this->assertFalse($result);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function test_download_without_illuminate_response(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content, 'image/svg+xml');

        ob_start();
        $result = $avatar->download('avatar.svg');
        $output = ob_get_clean();

        $this->assertNull($result);
        $this->assertSame($content, $output);
    }

    public function test_download_escapes_filename(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content, 'image/svg+xml');

        ob_start();
        $avatar->download('avatar"test.svg');
        ob_get_clean();

        $headers = xdebug_get_headers();
        $dispositionHeader = array_filter($headers, fn ($h): bool => str_starts_with((string) $h, 'Content-Disposition:'));

        if ($dispositionHeader !== []) {
            $this->assertStringContainsString('avatar\"test.svg', reset($dispositionHeader));
        }
    }

    public function test_stream_outputs_content_in_chunks(): void
    {
        $content = str_repeat('A', 10000); // 10KB content
        $avatar = new GeneratedAvatar($content, 'image/svg+xml');

        ob_start();
        $avatar->stream(1024, false); // Stream in 1KB chunks without headers
        $output = ob_get_clean();

        $this->assertSame($content, $output);
    }

    public function test_stream_with_headers(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content, 'image/svg+xml');

        ob_start();
        $avatar->stream(8192, true);
        $output = ob_get_clean();

        $this->assertSame($content, $output);
    }

    public function test_toPsr7Response_throws_exception_when_psr7_not_available(): void
    {
        $content = '<svg></svg>';
        $avatar = new GeneratedAvatar($content);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('PSR-7 support requires');

        $avatar->toPsr7Response();
    }

    public function test_alt_text_sets_custom_alt(): void
    {
        $content = '<svg xmlns="http://www.w3.org/2000/svg"></svg>';
        $avatar = new GeneratedAvatar($content, 'image/svg+xml', 'John Doe');

        $avatar->alt('Custom alt text');
        $html = $avatar->toHtml();

        $this->assertStringContainsString('Custom alt text', $html);
    }

    public function test_alt_text_uses_name_when_not_set(): void
    {
        $content = '<svg xmlns="http://www.w3.org/2000/svg"></svg>';
        $avatar = new GeneratedAvatar($content, 'image/svg+xml', 'John Doe');

        $html = $avatar->toHtml();

        $this->assertStringContainsString('Avatar for John Doe', $html);
    }

    public function test_accessible_html_with_data_uri(): void
    {
        $content = 'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=';
        $avatar = new GeneratedAvatar($content, 'image/svg+xml', 'John Doe');

        $html = $avatar->accessibleHtml();

        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('aria-label="Avatar for John Doe"', $html);
    }
}
