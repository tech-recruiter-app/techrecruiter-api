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

        $foreignIP = $this->resolveIP($foreignHost);
        $foreignPort = $parsedLink->getPort() ?? ($parsedLink->getScheme() === 'https' ? 443 : 80);

        // Verify that the link exists
        try {
            $response = Http::withOptions([
                'curl' => [
                    CURLOPT_RESOLVE => ["{$foreignHost}:{$foreignPort}:{$foreignIP}"],
                ],
            ])->timeout(1)->async(false)->head($parsedLink->toAsciiString());
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
     * Resolves a hostname to an IP address.
     *
     * Perform a Forward DNS lookup while preventing server-side request forgery attacks.
     */
    private function resolveIP(string $hostname): string
    {
        // Check if host is already an IP literal
        if (filter_var($hostname, FILTER_VALIDATE_IP)) {
            if (! filter_var($hostname, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new LinkVerificationException('Target IP is not allowed.');
            }

            return $hostname;
        }

        // Fetch IPv4 and IPv6 address resources
        if (($records = @dns_get_record($hostname, DNS_A | DNS_AAAA) ?: []) === []) {
            throw new LinkVerificationException("Unable to resolve host [$hostname].");
        }

        $ip = null;

        foreach ($records as $record) {
            $possibleIp = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($possibleIp && ! filter_var($possibleIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new LinkVerificationException('Target IP is not allowed.');
            }
            if (
                $possibleIp && $ip === null ||
                $possibleIp && filter_var($possibleIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            ) {
                $ip = $possibleIp;
            }
        }

        if (is_null($ip)) {
            throw new LinkVerificationException("No valid IP found for host [$hostname].");
        }

        assert(is_string($ip));

        return $ip;
    }
}
