<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\Domain\InvalidAddressComponentException;
use App\Values\Address;

interface AddressValidator
{
    /**
     * Validates an address.
     *
     * @param  Address  $address  The address to validate
     *
     * @throws InvalidAddressComponentException If an address component is not valid
     */
    public function validate(Address $address): void;
}
