<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Exceptions;

use RuntimeException;
use Throwable;

class EngineFailedException extends RuntimeException
{
    public function __construct(
        public readonly string $engineName,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        if ($message === '') {
            $message = "Engine '{$engineName}' failed to generate avatar.";
        }

        parent::__construct($message, $code, $previous);
    }

    public function getEngineName(): string
    {
        return $this->engineName;
    }
}
