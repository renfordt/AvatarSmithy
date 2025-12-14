<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Engines\Traits;

use Renfordt\AvatarSmithy\Support\Name;

trait PixelMatrixTrait
{
    /**
     * @return array<array<bool>>
     */
    protected function generateSymmetricMatrix(Name $name, int $pixels): array
    {
        $hash = $name->getHash();
        $symmetryMatrix = $this->getSymmetryMatrix($pixels);
        $divider = count($symmetryMatrix);
        $matrix = [];

        for ($i = 0; $i < $pixels ** 2; $i++) {
            $index = (int) ($i / 3);
            $data = $this->convertStrToBool(substr($hash, $i, 1));

            foreach ($symmetryMatrix[$i % $divider] as $item) {
                $matrix[$index][$item] = $data;
            }
        }

        return $matrix;
    }

    /**
     * @return array<array<int>>
     */
    protected function getSymmetryMatrix(int $pixels): array
    {
        $items = [];
        $i = $pixels - 1;

        for ($x = 0; $x <= $i / 2; $x++) {
            $items[$x] = [$x];
            if ($x !== $i - $x) {
                $items[$x][] = $i - $x;
            }
        }

        return $items;
    }

    /**
     * @return array<array<bool>>
     */
    protected function generateMatrix(Name $name, int $pixels): array
    {
        $hash = hash('sha256', $name->getHash());
        $matrix = [];

        for ($i = 0; $i < $pixels ** 2; $i++) {
            $matrix[$i % $pixels][(int) floor($i / $pixels)] = $this->convertStrToBool(substr($hash, $i, 1));
        }

        return $matrix;
    }

    protected function convertStrToBool(string $char): bool
    {
        return (bool) round(hexdec($char) / 10);
    }
}
