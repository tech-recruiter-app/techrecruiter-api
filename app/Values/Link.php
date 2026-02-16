<?php

declare(strict_types=1);

namespace App\Values;

use App\Contracts\LinkVerifier;
use App\Exceptions\Domain\LinkVerificationException;
use App\Exceptions\Domain\RuleViolationException;
use App\Exceptions\Domain\ValidationFailedException;
use JsonSerializable;
use Stringable;
use Uri\WhatWg\Url;

final readonly class Link implements JsonSerializable, Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        // Validate value as URL
        if (is_null($url = Url::parse($value))) {
            throw new ValidationFailedException("The link given [$value] is invalid.");
        }

        // Verify link has acceptable schemes
        if (! in_array($url->getScheme(), ['http', 'https'], true)) {
            throw new ValidationFailedException("The link given [$value] must contain the HTTP scheme.");
        }

        // Verify link has path
        if (blank($url->getPath()) || $url->getPath() === '/') {
            throw new RuleViolationException("The link given [$value] must have a path.");
        }

        $this->value = $url->toAsciiString();
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
     * Verifies that the link exists.
     *
     * @throws RuleViolationException If verification fails
     */
    public function verify(LinkVerifier $verifier): void
    {
        try {
            $verifier->verify($this);
        } catch (LinkVerificationException $e) {
            throw new RuleViolationException("Fake link provided: {$e->getMessage()}");
        }
    }
}
