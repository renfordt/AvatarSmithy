<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines;

class DiceBearEngine extends AbstractEngine
{
    /**
     * @param array<string, mixed> $options
     */
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        $style = is_string($options['style'] ?? null) ? $options['style'] : 'avataaars';
        $params = ['seed' => $seed, 'size' => (string) $size];

        if (isset($options['backgroundColor'])) {
            $colors = is_array($options['backgroundColor']) ? $options['backgroundColor'] : [$options['backgroundColor']];
            $params['backgroundColor'] = implode(',', array_map(fn ($c): string => is_string($c) ? ltrim($c, '#') : '', $colors));
        }

        if (isset($options['radius']) && is_int($options['radius'])) {
            $params['radius'] = (string) $options['radius'];
        }

        $url = 'https://api.dicebear.com/9.x/' . $style . '/svg?' . http_build_query($params);

        return $this->fetchUrl($url);
    }
}
