<?php

declare(strict_types=1);

namespace App\Traits;

use App\Values\Address;

trait UnserializesAddresses
{
    use RetrievesModelAttributes;

    /**
     * Creates an Address value object from the given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array{country: string, administrative_area: string, municipality: string, street: string, postal_code: string}  $keys
     */
    protected function unserializeAddress(array $attributes, array $keys): Address
    {
        return new Address(
            $this->getModelAttribute($keys['country'], $attributes, 'string'),
            $this->getModelAttribute($keys['administrative_area'], $attributes, 'string', true),
            $this->getModelAttribute($keys['municipality'], $attributes, 'string'),
            $this->getModelAttribute($keys['street'], $attributes, 'string', true),
            $this->getModelAttribute($keys['postal_code'], $attributes, 'string', true),
        );
    }
}
