<?php

declare(strict_types=1);

namespace App\Casts;

use App\Values\Email;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<Email, Email>
 */
final class AsEmail implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Email
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException("The $key's value must be a string.");
        }

        return new Email($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (! $value instanceof Email) {
            throw new InvalidArgumentException("The $key value given must be of type ".Email::class);
        }

        return (string) $value;
    }
}
