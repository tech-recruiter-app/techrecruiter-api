<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use LogicException;
use Throwable;

/**
 * Exception that represents validation error that should be caught
 * at the application layer but unexpectedly reached domain layer.
 */
final class ValidationFailedException extends LogicException
{
    /**
     * @param  non-empty-string  $message
     */
    public function __construct(
        string $message,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
