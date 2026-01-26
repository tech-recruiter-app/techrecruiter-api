<?php

declare(strict_types=1);

namespace App\Casts;

use App\Values\Name;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * @implements CastsAttributes<Name, Name>
 */
final class AsName implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Name
    {
        if (! array_key_exists('first_name', $attributes)) {
            throw new OutOfRangeException("Missing required 'first_name' attribute.");
        }
        if (! is_string($attributes['first_name'])) {
            throw new InvalidArgumentException("The 'first_name' attribute's value must be a string.");
        }

        if (! array_key_exists('last_name', $attributes)) {
            throw new OutOfRangeException("Missing required 'last_name' attribute.");
        }
        if (! is_string($attributes['last_name'])) {
            throw new InvalidArgumentException("The 'last_name' attribute's value must be a string.");
        }

        return new Name(
            $attributes['first_name'],
            $attributes['last_name'],
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $value instanceof Name) {
            throw new InvalidArgumentException("The $key value given must be of type ".Name::class);
        }

        return [
            'first_name' => $value->firstname,
            'last_name' => $value->lastname,
        ];
    }
}
