<?php

declare(strict_types=1);

namespace App\Values;

use App\Exceptions\Domain\ValidationFailedException;
use JsonSerializable;
use Stringable;

final readonly class JobTitle implements JsonSerializable, Stringable
{
    public function __construct(private string $value)
    {
        if (mb_strlen($value) <= 5) {
            throw new ValidationFailedException('The job title is too short.');
        }
        if (! preg_match('/^(?=.*[[:alpha:]]{3})[[:alpha:]\-\s]+$/', $value)) {
            throw new ValidationFailedException('The job title provided is not valid.');
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
