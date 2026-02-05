<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Exception;
use Throwable;

final class AddressVerificationException extends Exception
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
