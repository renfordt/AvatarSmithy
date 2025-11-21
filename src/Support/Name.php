<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Support;

use Renfordt\Colors\HexColor;

class Name
{
    /** @var array<string> */
    private readonly array $splitNames;

    private readonly string $hash;

    public function __construct(private readonly string $name)
    {
        $this->splitNames = array_filter(explode(' ', trim($this->name)));
        $this->hash = md5($this->name);
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function getInitials(): string
    {
        if ($this->splitNames === []) {
            return '';
        }

        $initials = '';
        foreach ($this->splitNames as $part) {
            $initials .= mb_substr($part, 0, 1, 'UTF-8');
        }

        return mb_strtoupper($initials, 'UTF-8');
    }

    public function getHexColor(int $offset = 0): HexColor
    {
        return HexColor::create('#' . substr($this->hash, $offset, 6));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHash(): string
    {
        return $this->hash;
    }
}
