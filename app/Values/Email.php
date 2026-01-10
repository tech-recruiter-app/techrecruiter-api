<?php

declare(strict_types=1);

namespace App\Values;

use App\Exceptions\Domain\ValidationFailedException;
use JsonSerializable;
use Stringable;

final readonly class Email implements JsonSerializable, Stringable
{
    public function __construct(public string $value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationFailedException('The value given is not a valid email address.');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}
