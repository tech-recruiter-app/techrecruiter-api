<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\Domain\DomainNameVerificationException;
use App\Values\CompanyDomain;

interface DomainNameVerifier
{
    /**
     * Verifies that a domain name exists.
     *
     * @param  CompanyDomain  $domain  The domain name to verify
     *
     * @throws DomainNameVerificationException If verification fails
     */
    public function verify(CompanyDomain $domain): void;
}
