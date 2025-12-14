<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Exceptions;

use RuntimeException;
use Throwable;

class AllEnginesFailedException extends RuntimeException
{
    /**
     * @param  array<int, array{engine: string, error: string, exception: ?Throwable}>  $failures
     */
    public function __construct(
        public readonly array $failures,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        if ($message === '') {
            $engineNames = array_map(fn (array $f): string => $f['engine'], $failures);
            $message = 'All avatar engines failed to generate an avatar. Tried: '.implode(', ', $engineNames);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<int, array{engine: string, error: string, exception: ?Throwable}>
     */
    public function getFailures(): array
    {
        return $this->failures;
    }

    /**
     * Get a summary of all failures as a string.
     */
    public function getFailureSummary(): string
    {
        $summary = "Avatar generation failed for all engines:\n";

        foreach ($this->failures as $failure) {
            $summary .= "  - {$failure['engine']}: {$failure['error']}\n";
        }

        return $summary;
    }
}
