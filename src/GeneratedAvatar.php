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

        // For inline SVG, add accessibility attributes
        return $this->addSvgAccessibility($this->content);
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

        // For inline SVG, add full accessibility attributes
        return $this->addSvgAccessibility($this->content);
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

    public function toString(): string
    {
        return $this->content;
    }

    public function __toString(): string
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
