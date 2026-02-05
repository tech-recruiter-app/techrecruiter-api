<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\Domain\AddressVerificationException;
use App\Values\Address;

interface AddressVerifier
{
    /**
     * Verifies that an address exists.
     *
     * @param  Address  $address  The address to verify
     *
     * @throws AddressVerificationException If verification fails
     */
    public function verify(Address $address): void;
}
