<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Exceptions;

use InvalidArgumentException;

class ValidationException extends InvalidArgumentException
{
    public static function invalidSize(int $size, int $min = 8, int $max = 2048): self
    {
        return new self("Invalid size '{$size}'. Size must be between {$min} and {$max} pixels.");
    }

    public static function invalidLightness(float $value, string $field = 'lightness'): self
    {
        return new self("Invalid {$field} value '{$value}'. Must be between 0.0 and 1.0.");
    }

    public static function invalidPositiveInteger(int $value, string $field): self
    {
        return new self("Invalid {$field} value '{$value}'. Must be greater than 0.");
    }

    public static function invalidNonNegativeInteger(int $value, string $field): self
    {
        return new self("Invalid {$field} value '{$value}'. Must be greater than or equal to 0.");
    }
}
