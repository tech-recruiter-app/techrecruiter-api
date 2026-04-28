<?php

declare(strict_types=1);

namespace App\Values;

use DomainException;
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
            throw new DomainException("The link [$value] must be a valid URL.");
        }

        // Verify link has acceptable schemes
        if (! in_array($url->getScheme(), ['http', 'https'], true)) {
            throw new DomainException("The link [$value] must have an acceptable scheme (e.g., http, https).");
        }

        // Verify link has path
        if (blank($url->getPath()) || $url->getPath() === '/') {
            throw new DomainException("The link [$value] must have a valid path.");
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
}
