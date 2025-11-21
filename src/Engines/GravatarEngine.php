<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

class GravatarEngine extends AbstractEngine
{
    protected string $contentType = 'text/html';

    /**
     * @param array<string, mixed> $options
     */
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        $email = $seed;
        $hash = md5(strtolower(trim($email)));

        $default = $options['default'] ?? 'mp';
        $rating = $options['rating'] ?? 'g';

        $params = [
            'd' => $default,
            'r' => $rating,
            's' => (string) $size,
            'f' => 'y',
        ];

        return 'https://www.gravatar.com/avatar/' . $hash . '?' . http_build_query($params);
    }
}
