<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use RuntimeException;
use Throwable;

/**
 * Exception that represents a violation of a business rule.
 */
final class RuleViolationException extends RuntimeException
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
