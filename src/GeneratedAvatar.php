<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy;

class GeneratedAvatar implements \Stringable
{
    public function __construct(
        protected string $content,
        protected string $contentType = 'image/svg+xml'
    ) {
    }

    public function toHtml(): string
    {
        if ($this->isDataUri()) {
            return '<img src="' . $this->content . '" />';
        }

        if ($this->isUrl()) {
            return '<img src="' . $this->content . '" />';
        }

        return $this->content;
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
