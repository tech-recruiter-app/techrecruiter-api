<?php

declare(strict_types=1);

namespace App\Values;

use App\Exceptions\Domain\RuleViolationException;
use App\Exceptions\Domain\ValidationFailedException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
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
    public function verify(): void
    {
        $url = new Url($this->value);

        // Skip verification if resource is located in our domain
        $appHost = is_string($appUrl = config('app.url')) ? new Url($appUrl)->getAsciiHost() : null;
        if ($url->getAsciiHost() === $appHost) {
            return;
        }

        $this->shieldFromSSRFattacks($url);

        // Verify that link exists
        try {
            $response = Http::timeout(1)->async(false)->head($url->toAsciiString());
        } catch (ConnectionException) {
            throw new RuleViolationException("The resource at [{$url->toAsciiString()}] is not accessible.");
        }
        if (! $response->successful()) {
            throw new RuleViolationException("The resource at [{$url->toAsciiString()}] does not exist.");
        }

        // Verify that link has accepted document type
        $contentType = strtok($response->header('Content-Type'), ';');
        if (! in_array(mb_trim((string) $contentType), ['application/pdf', 'application/msword'], true)) {
            throw new RuleViolationException("The resource at [{$url->toAsciiString()}] does not have an accepted type.");
        }
    }

    /**
     * Protects against server-side request forgery attacks.
     *
     * Verifies link's host IP address is not within private ranges, loopback, and reserved addresses.
     */
    private function shieldFromSSRFattacks(Url $url): void
    {
        // Check if host is already an IP literal
        if (filter_var(($host = $url->getAsciiHost() ?? ''), FILTER_VALIDATE_IP)) {
            if (! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new RuleViolationException('Target IP is not allowed.');
            }

            return;
        }
        // Resolve both A and AAAA records
        if (($records = dns_get_record($host, DNS_A | DNS_AAAA) ?: []) === []) {
            throw new RuleViolationException("Unable to resolve host [$host].");
        }
        foreach ($records as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($ip && ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new RuleViolationException('Target IP is not allowed.');
            }
        }
    }
}
