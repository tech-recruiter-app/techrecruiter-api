<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\LinkVerifier as LinkVerifierContract;
use App\Exceptions\Domain\LinkVerificationException;
use App\Values\Link;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Uri\WhatWg\Url;

final readonly class LinkVerifier implements LinkVerifierContract
{
    /**
     * @param  non-empty-string  $localHost  Hostname of the current server.
     */
    public function __construct(private string $localHost) {}

    public function verify(Link $link): void
    {
        $parsedLink = new Url((string) $link);
        $foreignHost = (string) $parsedLink->getAsciiHost();

        // Skip verification if resource is located on our servers
        if ($foreignHost === $this->localHost) {
            return;
        }

        $this->shieldFromSSRFattacks($parsedLink);

        // Verify that the link exists
        try {
            $response = Http::timeout(1)->async(false)->head($parsedLink->toAsciiString());
        } catch (ConnectionException) {
            throw new LinkVerificationException("The resource at [{$parsedLink->toAsciiString()}] is not accessible.");
        }
        if (! $response->successful()) {
            throw new LinkVerificationException("The resource at [{$parsedLink->toAsciiString()}] does not exist.");
        }

        // Verify that the link has accepted document type
        $contentType = strtok($response->header('Content-Type'), ';');
        if (! in_array(mb_trim((string) $contentType), [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ], true)) {
            throw new LinkVerificationException("The resource at [{$parsedLink->toAsciiString()}] does not have an accepted type.");
        }
    }

    /**
     * Protects against server-side request forgery attacks.
     *
     * Verifies that the foreign host IP address is not within private ranges, loopback, and reserved addresses.
     */
    private function shieldFromSSRFattacks(Url $url): void
    {
        // Check if host is already an IP literal
        if (filter_var(($host = $url->getAsciiHost() ?? ''), FILTER_VALIDATE_IP)) {
            if (! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new LinkVerificationException('Target IP is not allowed.');
            }

            return;
        }
        // Resolve both A and AAAA records
        if (($records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: []) === []) {
            throw new LinkVerificationException("Unable to resolve host [$host].");
        }
        foreach ($records as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($ip && ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new LinkVerificationException('Target IP is not allowed.');
            }
        }
    }
}
