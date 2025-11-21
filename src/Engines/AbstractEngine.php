<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

abstract class AbstractEngine implements EngineInterface
{
    protected string $contentType = 'image/svg+xml';

    public function getContentType(): string
    {
        return $this->contentType;
    }

    protected function fetchUrl(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'user_agent' => 'AvatarSmithy/1.0',
            ],
        ]);

        $content = @file_get_contents($url, false, $context);

        return $content !== false ? $content : null;
    }
}
