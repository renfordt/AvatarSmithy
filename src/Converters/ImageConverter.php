<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Converters;

use RuntimeException;
use SVG\SVG;

class ImageConverter
{
    /**
     * Convert SVG content to PNG format.
     *
     * @param string $content The SVG content or data URI
     * @param int $width Target width in pixels
     * @param int $height Target height in pixels
     * @param int $quality PNG compression level (0-9 for Imagick, 0-9 for GD)
     * @return string PNG image data
     * @throws RuntimeException If conversion fails
     */
    public static function toPng(string $content, int $width, int $height, int $quality = 9): string
    {
        if (extension_loaded('imagick')) {
            return self::convertWithImagick($content, 'png', $width, $height, $quality);
        }

        return self::convertWithGd($content, 'png', $width, $height, $quality);
    }

    /**
     * Convert SVG content to JPG format.
     *
     * @param string $content The SVG content or data URI
     * @param int $width Target width in pixels
     * @param int $height Target height in pixels
     * @param int $quality JPEG quality (0-100)
     * @return string JPG image data
     * @throws RuntimeException If conversion fails
     */
    public static function toJpg(string $content, int $width, int $height, int $quality = 90): string
    {
        if (extension_loaded('imagick')) {
            return self::convertWithImagick($content, 'jpg', $width, $height, $quality);
        }

        return self::convertWithGd($content, 'jpg', $width, $height, $quality);
    }

    /**
     * Convert SVG content to WebP format.
     *
     * @param string $content The SVG content or data URI
     * @param int $width Target width in pixels
     * @param int $height Target height in pixels
     * @param int $quality WebP quality (0-100)
     * @return string WebP image data
     * @throws RuntimeException If conversion fails
     */
    public static function toWebp(string $content, int $width, int $height, int $quality = 90): string
    {
        if (extension_loaded('imagick')) {
            return self::convertWithImagick($content, 'webp', $width, $height, $quality);
        }

        return self::convertWithGd($content, 'webp', $width, $height, $quality);
    }

    /**
     * Convert image using Imagick extension (preferred method).
     *
     * @param string $content The SVG content or data URI
     * @param string $format Target format (png, jpg, webp)
     * @param int $width Target width in pixels
     * @param int $height Target height in pixels
     * @param int $quality Quality/compression level
     * @return string Converted image data
     * @throws RuntimeException If conversion fails
     */
    protected static function convertWithImagick(string $content, string $format, int $width, int $height, int $quality): string
    {
        try {
            $imagick = new \Imagick();

            // Remove data URI prefix if present
            $svgContent = self::extractSvgContent($content);

            // Load SVG content
            $imagick->readImageBlob($svgContent);

            // Set resolution for better quality
            $imagick->setResolution($width, $height);

            // Resize to target dimensions
            $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);

            // Set format and quality
            $imagick->setImageFormat($format);

            if ($format === 'png') {
                // PNG compression level (0-9)
                $imagick->setImageCompressionQuality($quality * 10);
            } elseif ($format === 'jpg' || $format === 'jpeg') {
                // For JPEG, add white background (JPEG doesn't support transparency)
                $imagick->setImageBackgroundColor('white');
                $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $imagick->setImageCompressionQuality($quality);
            } else {
                // WebP and other formats
                $imagick->setImageCompressionQuality($quality);
            }

            $imageData = $imagick->getImageBlob();
            $imagick->clear();
            $imagick->destroy();

            return $imageData;
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to convert image with Imagick: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Convert image using GD extension with meyfa/php-svg renderer (fallback method).
     *
     * @param string $content The SVG content or data URI
     * @param string $format Target format (png, jpg, webp)
     * @param int $width Target width in pixels
     * @param int $height Target height in pixels
     * @param int $quality Quality/compression level
     * @return string Converted image data
     * @throws RuntimeException If conversion fails or GD not available
     */
    protected static function convertWithGd(string $content, string $format, int $width, int $height, int $quality): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('GD extension is required for image conversion. Install GD or Imagick extension.');
        }

        try {
            // Remove data URI prefix if present
            $svgContent = self::extractSvgContent($content);

            // Parse SVG with meyfa/php-svg
            $svg = SVG::fromString($svgContent);

            if (!$svg instanceof \SVG\SVG) {
                throw new RuntimeException('Failed to parse SVG content');
            }

            // Validate dimensions
            if ($width < 1 || $height < 1) {
                throw new RuntimeException('Image dimensions must be at least 1x1 pixels');
            }

            // Create GD image resource
            $image = imagecreatetruecolor($width, $height);

            if ($image === false) {
                throw new RuntimeException('Failed to create GD image resource');
            }

            // Enable alpha blending for transparency (PNG/WebP)
            if ($format === 'png' || $format === 'webp') {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);

                if ($transparent === false) {
                    throw new RuntimeException('Failed to allocate transparent color');
                }

                imagefilledrectangle($image, 0, 0, $width, $height, $transparent);
                imagealphablending($image, true);
            } else {
                // JPG: fill with white background
                $white = imagecolorallocate($image, 255, 255, 255);

                if ($white === false) {
                    throw new RuntimeException('Failed to allocate white color');
                }

                imagefilledrectangle($image, 0, 0, $width, $height, $white);
            }

            // Render SVG to GD image
            $rasterizer = $svg->toRasterImage($width, $height);

            // @phpstan-ignore argument.type (PHPDoc says resource, but returns GdImage in PHP 8+)
            imagecopy($image, $rasterizer, 0, 0, 0, 0, $width, $height);

            // Convert to target format
            ob_start();
            $success = match ($format) {
                'png' => imagepng($image, null, min(9, max(0, $quality))),
                'jpg', 'jpeg' => imagejpeg($image, null, min(100, max(0, $quality))),
                'webp' => function_exists('imagewebp')
                    ? imagewebp($image, null, min(100, max(0, $quality)))
                    : throw new RuntimeException('WebP support not available in GD. Install Imagick or use PNG/JPG.'),
                default => throw new RuntimeException("Unsupported format: {$format}"),
            };

            $imageData = ob_get_clean();
            imagedestroy($image);

            if (! $success || $imageData === false) {
                throw new RuntimeException("Failed to encode {$format} image");
            }

            return $imageData;
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to convert image with GD: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Extract SVG content from data URI or return raw content.
     *
     * @param string $content Content that may be a data URI or raw SVG
     * @return string Raw SVG content
     */
    protected static function extractSvgContent(string $content): string
    {
        // Handle data URI (e.g., "data:image/svg+xml;base64,...")
        if (str_starts_with($content, 'data:')) {
            $parts = explode(',', $content, 2);

            if (count($parts) === 2) {
                $header = $parts[0];
                $data = $parts[1];

                // Check if it's base64 encoded
                if (str_contains($header, 'base64')) {
                    $decoded = base64_decode($data, true);
                    return $decoded !== false ? $decoded : $data;
                }

                // URL encoded
                return urldecode($data);
            }
        }

        return $content;
    }

    /**
     * Get MIME type for format.
     *
     * @param string $format Image format (png, jpg, webp)
     * @return string MIME type
     */
    public static function getMimeType(string $format): string
    {
        return match ($format) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
