<?php

declare(strict_types=1);

namespace App\Values;

use DomainException;
use JsonSerializable;
use Stringable;

final readonly class CompanyDomain implements JsonSerializable, Stringable
{
    public function __construct(private string $value)
    {
        if (! filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new DomainException("The domain name given [$value] is not valid.");
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
