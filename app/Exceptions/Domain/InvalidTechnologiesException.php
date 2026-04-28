<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use RuntimeException;

final class InvalidTechnologiesException extends RuntimeException
{
    /**
     * @param  list<string>  $invalidTechnologies  List of invalid technology names found in a stack.
     * @param  list<string>  $stack  The technology stack that was validated.
     */
    public function __construct(
        public readonly array $invalidTechnologies,
        public readonly array $stack
    ) {
        parent::__construct(sprintf(
            'Invalid technology names found in the stack: %s. Stack: %s',
            implode(', ', $invalidTechnologies),
            implode(', ', $stack)
        ));
    }
}
