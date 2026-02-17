<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DomainNameVerifier as DomainNameVerifierContract;
use App\Exceptions\Domain\DomainNameVerificationException;
use App\Values\CompanyDomain;

final class DomainNameVerifier implements DomainNameVerifierContract
{
    public function verify(CompanyDomain $domain): void
    {
        if (! checkdnsrr((string) $domain, 'A') && ! checkdnsrr((string) $domain, 'AAAA')) {
            throw new DomainNameVerificationException(sprintf('The domain name given [%s] does not exist.', (string) $domain));
        }
    }
}
