<?php

declare(strict_types=1);

namespace App\Values;

use App\Exceptions\Domain\RuleViolationException;
use JsonSerializable;
use RuntimeException;
use Stringable;

final class CompanyDomain implements JsonSerializable, Stringable
{
    /**
     * Indicates whether domain exists or not.
     */
    public private(set) bool $exists = false;

    public function __construct(public readonly string $value)
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
     * @throws RuntimeException If the domain's existence was previously verified
     */
    public function verify(): void
    {
        if ($this->exists) {
            throw new RuntimeException("The domain's existence has already been verified.");
        }

        if (! checkdnsrr($this->value, 'A') && ! checkdnsrr($this->value, 'AAAA')) {
            throw new RuleViolationException("The domain name given [{$this->value}] does not exist.");
        }

        $this->exists = true;
    }
}
