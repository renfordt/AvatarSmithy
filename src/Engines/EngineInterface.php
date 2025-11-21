<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

interface EngineInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function generate(string $seed, ?string $name, int $size, array $options): ?string;

    public function getContentType(): string;
}
