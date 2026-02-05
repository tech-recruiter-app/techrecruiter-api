<?php

declare(strict_types=1);

namespace App\Values;

use App\Exceptions\Domain\RuleViolationException;
use JsonSerializable;
use Stringable;

final readonly class CompanyDomain implements JsonSerializable, Stringable
{
    public function __construct(public string $value)
    {
        if (! filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new RuleViolationException("The domain name given [$value] is not valid.");
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

    /**
     * Verifies the domain exists.
     *
     * @throws RuleViolationException If verification fails
     */
    public function verify(): void
    {
        if (! checkdnsrr($this->value, 'A') && ! checkdnsrr($this->value, 'AAAA')) {
            throw new RuleViolationException("The domain name given [{$this->value}] does not exist.");
        }
    }
}
