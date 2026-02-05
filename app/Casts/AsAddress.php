<?php

declare(strict_types=1);

namespace App\Casts;

use App\Traits\UnserializesAddresses;
use App\Values\Address;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<Address, Address>
 */
final class AsAddress implements CastsAttributes
{
    use UnserializesAddresses;

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Address
    {
        return $this->unserializeAddress($attributes, [
            'country' => 'address_country',
            'administrative_area' => 'address_administrative_area',
            'municipality' => 'address_municipality',
            'street' => 'address_street',
            'postal_code' => 'address_postal_code',
        ]);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $value instanceof Address) {
            throw new InvalidArgumentException("The $key value given must be of type ".Address::class);
        }

        return [
            'address_country' => $value->country,
            'address_administrative_area' => $value->administrativeArea,
            'address_municipality' => $value->municipality,
            'address_street' => $value->street,
            'address_postal_code' => $value->postalCode,
        ];
    }
}
