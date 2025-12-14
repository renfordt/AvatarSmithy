<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy;

use Renfordt\AvatarSmithy\Converters\ImageConverter;

class GeneratedAvatar implements \Stringable
{
    protected ?string $altText = null;

    public function __construct(protected string $content, protected string $contentType = 'image/svg+xml', protected ?string $name = null, protected int $size = 200)
    {
    }

    /**
     * Set alt text for the avatar image.
     *
     * @param string $altText Alternative text for accessibility
     */
    public function alt(string $altText): self
    {
        $this->altText = $altText;

        return $this;
    }

    /**
     * Generate default alt text based on name.
     */
    protected function getDefaultAltText(): string
    {
        if ($this->altText !== null) {
            return $this->altText;
        }

        if ($this->name !== null) {
            return "Avatar for {$this->name}";
        }

        return 'Avatar';
    }

    /**
     * Convert to HTML img tag (basic version without accessibility features).
     */
    public function toHtml(): string
    {
        $altText = htmlspecialchars($this->getDefaultAltText(), ENT_QUOTES, 'UTF-8');

        if ($this->isDataUri()) {
            return '<img src="' . $this->content . '" alt="' . $altText . '" />';
        }

        if ($this->isUrl()) {
            return '<img src="' . $this->content . '" alt="' . $altText . '" />';
        }

        // For SVG content, use img tag with data URI for better cross-browser compatibility
        if ($this->contentType === 'image/svg+xml') {
            return '<img src="' . $this->toBase64() . '" alt="' . $altText . '" />';
        }

        // For other content types, return as-is
        return $this->content;
    }

    /**
     * Convert to accessible HTML with ARIA attributes.
     */
    public function accessibleHtml(): string
    {
        $altText = htmlspecialchars($this->getDefaultAltText(), ENT_QUOTES, 'UTF-8');

        if ($this->isDataUri() || $this->isUrl()) {
            $src = htmlspecialchars($this->isUrl() ? $this->content : $this->toBase64(), ENT_QUOTES, 'UTF-8');
            return '<img src="' . $src . '" alt="' . $altText . '" role="img" aria-label="' . $altText . '" />';
        }

        // For SVG content, use img tag with data URI and full accessibility attributes
        if ($this->contentType === 'image/svg+xml') {
            return '<img src="' . $this->toBase64() . '" alt="' . $altText . '" role="img" aria-label="' . $altText . '" />';
        }

        // For other content types, return as-is
        return $this->content;
    }

    /**
     * Normalize SVG dimensions by setting width/height to 100%.
     * This allows CSS to properly control SVG sizing, especially for SVGs
     * with large viewBox values.
     *
     * @param string $svgContent Raw SVG content
     * @return string Normalized SVG content
     */
    protected function normalizeSvgDimensions(string $svgContent): string
    {
        // Replace width attribute with 100%
        $normalized = preg_replace(
            '/<svg([^>]*)\s+width="[^"]*"/',
            '<svg$1 width="100%"',
            $svgContent,
            1
        );

        if ($normalized === null) {
            $normalized = $svgContent;
        }

        // Replace height attribute with 100%
        $normalized = preg_replace(
            '/<svg([^>]*)\s+height="[^"]*"/',
            '<svg$1 height="100%"',
            $normalized,
            1
        );

        return $normalized ?? $svgContent;
    }

    /**
     * Add accessibility attributes to SVG content.
     *
     * @param string $svgContent Raw SVG content
     * @return string SVG with accessibility attributes
     */
    protected function addSvgAccessibility(string $svgContent): string
    {
        $altText = htmlspecialchars($this->getDefaultAltText(), ENT_QUOTES, 'UTF-8');

        // Check if SVG already has accessibility attributes
        if (str_contains($svgContent, 'role="img"') && str_contains($svgContent, '<title>')) {
            return $svgContent;
        }

        // Add role="img" to opening SVG tag
        $svgContentWithRole = preg_replace(
            '/<svg([^>]*)>/',
            '<svg$1 role="img" aria-labelledby="avatar-title">',
            $svgContent,
            1
        );

        if ($svgContentWithRole === null) {
            return $this->content;
        }

        // Add title element after opening SVG tag
        $svgContentWithTitle = preg_replace(
            '/(<svg[^>]*>)/',
            '$1<title id="avatar-title">' . $altText . '</title>',
            $svgContentWithRole,
            1
        );

        return $svgContentWithTitle ?? $this->content;
    }

    public function toBase64(): string
    {
        if ($this->isDataUri()) {
            return $this->content;
        }

        return 'data:' . $this->contentType . ';base64,' . base64_encode($this->content);
    }

    public function toUrl(): string
    {
        if ($this->isUrl()) {
            return $this->content;
        }

        return $this->toBase64();
    }

    /**
     * Convert avatar to PNG format.
     *
     * @param int|null $width Target width (defaults to original size)
     * @param int|null $height Target height (defaults to original size)
     * @param int $quality PNG compression level (0-9, default: 9)
     * @return self New GeneratedAvatar instance with PNG content
     */
    public function toPng(?int $width = null, ?int $height = null, int $quality = 9): self
    {
        $width ??= $this->size;
        $height ??= $this->size;

        $pngData = ImageConverter::toPng($this->content, $width, $height, $quality);

        return new self($pngData, ImageConverter::getMimeType('png'), $this->name, $width);
    }

    /**
     * Convert avatar to JPG format.
     *
     * @param int|null $width Target width (defaults to original size)
     * @param int|null $height Target height (defaults to original size)
     * @param int $quality JPEG quality (0-100, default: 90)
     * @return self New GeneratedAvatar instance with JPG content
     */
    public function toJpg(?int $width = null, ?int $height = null, int $quality = 90): self
    {
        $width ??= $this->size;
        $height ??= $this->size;

        $jpgData = ImageConverter::toJpg($this->content, $width, $height, $quality);

        return new self($jpgData, ImageConverter::getMimeType('jpg'), $this->name, $width);
    }

    /**
     * Convert avatar to WebP format.
     *
     * @param int|null $width Target width (defaults to original size)
     * @param int|null $height Target height (defaults to original size)
     * @param int $quality WebP quality (0-100, default: 90)
     * @return self New GeneratedAvatar instance with WebP content
     */
    public function toWebp(?int $width = null, ?int $height = null, int $quality = 90): self
    {
        $width ??= $this->size;
        $height ??= $this->size;

        $webpData = ImageConverter::toWebp($this->content, $width, $height, $quality);

        return new self($webpData, ImageConverter::getMimeType('webp'), $this->name, $width);
    }

    public function toResponse(): mixed
    {
        if (class_exists('\Illuminate\Http\Response')) {
            return new \Illuminate\Http\Response($this->content, 200, [
                'Content-Type' => $this->contentType,
            ]);
        }

        header('Content-Type: ' . $this->contentType);
        echo $this->content;
        return null;
    }

    /**
     * Save avatar to a file.
     *
     * @param string $path Absolute or relative path where to save the avatar
     * @return bool True on success, false on failure
     */
    public function save(string $path): bool
    {
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return false;
        }

        $result = file_put_contents($path, $this->content);

        return $result !== false;
    }

    /**
     * Create a download response for the avatar.
     *
     * @param string $filename Filename for the download
     * @return mixed Response object or null
     */
    public function download(string $filename): mixed
    {
        if (class_exists('\Illuminate\Http\Response')) {
            return new \Illuminate\Http\Response($this->content, 200, [
                'Content-Type' => $this->contentType,
                'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
                'Content-Length' => (string) strlen($this->content),
            ]);
        }

        header('Content-Type: ' . $this->contentType);
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . strlen($this->content));
        echo $this->content;
        return null;
    }

    /**
     * Create a PSR-7 response for the avatar.
     *
     * Requires a PSR-17 HTTP factory implementation (e.g., nyholm/psr7, guzzle/psr7, laminas/laminas-diactoros).
     *
     * @param \Psr\Http\Message\ResponseFactoryInterface|null $responseFactory Optional PSR-17 response factory
     * @param \Psr\Http\Message\StreamFactoryInterface|null $streamFactory Optional PSR-17 stream factory
     * @throws \RuntimeException If PSR-7/PSR-17 interfaces are not available
     */
    public function toPsr7Response(
        ?\Psr\Http\Message\ResponseFactoryInterface $responseFactory = null,
        ?\Psr\Http\Message\StreamFactoryInterface $streamFactory = null
    ): \Psr\Http\Message\ResponseInterface {
        if (! interface_exists('\Psr\Http\Message\ResponseInterface')) {
            throw new \RuntimeException('PSR-7 support requires psr/http-message package. Install it via: composer require psr/http-message');
        }

        if (! interface_exists('\Psr\Http\Message\ResponseFactoryInterface')) {
            throw new \RuntimeException('PSR-17 support requires psr/http-factory package. Install it via: composer require psr/http-factory-implementation');
        }

        // Auto-discover PSR-17 implementation if not provided
        if (! $responseFactory instanceof \Psr\Http\Message\ResponseFactoryInterface || ! $streamFactory instanceof \Psr\Http\Message\StreamFactoryInterface) {
            $discovered = $this->discoverPsr17Factories();
            $responseFactory ??= $discovered['response'];
            $streamFactory ??= $discovered['stream'];
        }

        $response = $responseFactory->createResponse(200);
        $stream = $streamFactory->createStream($this->content);

        return $response
            ->withBody($stream)
            ->withHeader('Content-Type', $this->contentType)
            ->withHeader('Content-Length', (string) strlen($this->content));
    }

    /**
     * Stream the avatar content directly to output.
     *
     * Useful for large avatars to reduce memory usage.
     *
     * @param int $chunkSize Chunk size in bytes (default: 8192)
     * @param bool $sendHeaders Whether to send HTTP headers
     */
    public function stream(int $chunkSize = 8192, bool $sendHeaders = true): void
    {
        if ($sendHeaders) {
            header('Content-Type: ' . $this->contentType);
            header('Content-Length: ' . strlen($this->content));
        }

        // Stream the content in chunks
        $offset = 0;
        $length = strlen($this->content);

        while ($offset < $length) {
            $chunk = substr($this->content, $offset, $chunkSize);
            echo $chunk;
            flush();
            $offset += $chunkSize;
        }
    }

    /**
     * Auto-discover PSR-17 factory implementations.
     *
     * @return array{response: \Psr\Http\Message\ResponseFactoryInterface, stream: \Psr\Http\Message\StreamFactoryInterface}
     * @throws \RuntimeException If no PSR-17 implementation is found
     */
    protected function discoverPsr17Factories(): array
    {
        // List of common PSR-17 implementations in order of preference
        $implementations = [
            'Nyholm\Psr7\Factory\Psr17Factory',
            'GuzzleHttp\Psr7\HttpFactory',
            'Laminas\Diactoros\ResponseFactory',
            'Slim\Psr7\Factory\ResponseFactory',
        ];

        foreach ($implementations as $factoryClass) {
            if (class_exists($factoryClass)) {
                $factory = new $factoryClass();

                if ($factory instanceof \Psr\Http\Message\ResponseFactoryInterface &&
                    $factory instanceof \Psr\Http\Message\StreamFactoryInterface) {
                    return ['response' => $factory, 'stream' => $factory];
                }
            }
        }

        // Check for separate implementations
        $responseFactory = null;
        $streamFactory = null;

        if (class_exists('Laminas\Diactoros\ResponseFactory')) {
            $responseFactory = new \Laminas\Diactoros\ResponseFactory();
        }

        if (class_exists('Laminas\Diactoros\StreamFactory')) {
            $streamFactory = new \Laminas\Diactoros\StreamFactory();
        }

        if ($responseFactory instanceof \Laminas\Diactoros\ResponseFactory && $streamFactory instanceof \Laminas\Diactoros\StreamFactory) {
            return ['response' => $responseFactory, 'stream' => $streamFactory];
        }

        throw new \RuntimeException(
            'No PSR-17 HTTP factory implementation found. Install one of: nyholm/psr7, guzzlehttp/psr7, laminas/laminas-diactoros'
        );
    }

    public function toString(): string
    {
        return $this->content;
    }

    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * Get raw SVG content.
     * For non-SVG content, returns the raw content.
     */
    public function toSvg(): string
    {
        return $this->content;
    }

    protected function isDataUri(): bool
    {
        return str_starts_with($this->content, 'data:');
    }

    protected function isUrl(): bool
    {
        return str_starts_with($this->content, 'http://') || str_starts_with($this->content, 'https://');
    }
}
