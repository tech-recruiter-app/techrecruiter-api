<?php

declare(strict_types=1);

namespace App\Values;

use App\Exceptions\Domain\InvalidAddress;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, string|null>
 */
final readonly class Address implements Arrayable, JsonSerializable
{
    /**
     * @param  string  $country  Country component of the address
     * @param  null|string  $administrativeArea  Top-level administrative area of the country component of the address
     * @param  string  $municipality  City/Town component of the address
     * @param  null|string  $street  Street address component of the address
     * @param  null|string  $postalCode  Postal code component of the address
     */
    public function __construct(
        public string $country,
        public ?string $administrativeArea,
        public string $municipality,
        public ?string $street = null,
        public ?string $postalCode = null
    ) {
        if (! preg_match('/^[\p{L}\s\'-]{2,}$/u', $country)) {
            throw new InvalidAddress(
                "Invalid country name given: $country",
                InvalidAddress::INVALID_COUNTRY
            );
        }
        if (isset($administrativeArea) && ! preg_match('/^[\p{L}\s\'-]{2,}$/u', $administrativeArea)) {
            throw new InvalidAddress(
                "Invalid administrative area name given: $administrativeArea",
                InvalidAddress::INVALID_ADMINISTRATIVE_AREA
            );
        }
        if (! preg_match('/^[\p{L}\s\'-]{3,}$/u', $municipality)) {
            throw new InvalidAddress(
                "Invalid municipality name given: $municipality",
                InvalidAddress::INVALID_MUNICIPALITY
            );
        }
        if (isset($street) && (mb_strlen($street) < 4 || ! preg_match('/^([[:digit:]]{1,6}\s)?[\p{L}\p{N}\s\'-]+$/u', $street))) {
            throw new InvalidAddress(
                "[$street] has an incorrect street address format.",
                InvalidAddress::INVALID_STREET
            );
        }
        if (isset($postalCode) && (mb_strlen($postalCode) < 3 || ! preg_match('/^[[:alnum:]\s-]+$/', $postalCode))) {
            throw new InvalidAddress(
                "Invalid postal code given: $postalCode",
                InvalidAddress::INVALID_POSTAL_CODE
            );
        }
    }

    /**
     * @return array{country: string, administrativeArea: string|null, municipality: string, street: string|null, postalCode: string|null}
     */
    public function toArray(): array
    {
        return [
            'country' => $this->country,
            'administrativeArea' => $this->administrativeArea,
            'municipality' => $this->municipality,
            'street' => $this->street,
            'postalCode' => $this->postalCode,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
