<?php

declare(strict_types=1);

namespace App\Casts;

use App\Values\Address;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use LogicException;
use OutOfRangeException;
use ReflectionProperty;

/**
 * @implements CastsAttributes<Address, Address>
 */
final class AsAddress implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Address
    {
        if (! array_key_exists('address_country', $attributes)) {
            throw new OutOfRangeException("Missing required 'address_country' attribute.");
        }
        if (! is_string($attributes['address_country'])) {
            throw new InvalidArgumentException("The 'address_country' attribute's value must be a string.");
        }

        if (! array_key_exists('address_administrative_area', $attributes)) {
            throw new OutOfRangeException("Missing required 'address_administrative_area' attribute.");
        }
        if (! is_null($attributes['address_administrative_area']) && ! is_string($attributes['address_administrative_area'])) {
            throw new InvalidArgumentException("The 'address_administrative_area' attribute's value must be a string.");
        }

        if (! array_key_exists('address_municipality', $attributes)) {
            throw new OutOfRangeException("Missing required 'address_municipality' attribute.");
        }
        if (! is_string($attributes['address_municipality'])) {
            throw new InvalidArgumentException("The 'address_municipality' attribute's value must be a string.");
        }

        if (! array_key_exists('address_street', $attributes)) {
            throw new OutOfRangeException("Missing required 'address_street' attribute.");
        }
        if (! is_null($attributes['address_street']) && ! is_string($attributes['address_street'])) {
            throw new InvalidArgumentException("The 'address_street' attribute's value must be a string.");
        }

        if (! array_key_exists('address_postal_code', $attributes)) {
            throw new OutOfRangeException("Missing required 'address_postal_code' attribute.");
        }
        if (! is_null($attributes['address_postal_code']) && ! is_string($attributes['address_postal_code'])) {
            throw new InvalidArgumentException("The 'address_postal_code' attribute's value must be a string.");
        }

        $address = new Address(
            $attributes['address_country'],
            $attributes['address_administrative_area'],
            $attributes['address_municipality'],
            $attributes['address_street'],
            $attributes['address_postal_code'],
        );
        new ReflectionProperty(Address::class, 'isValid')->setValue($address, true);

        return $address;
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

        if (! $value->isValid) {
            throw new LogicException('The address must be valid in order to be saved.');
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
