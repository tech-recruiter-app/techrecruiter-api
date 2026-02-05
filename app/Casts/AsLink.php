<?php

declare(strict_types=1);

namespace App\Casts;

use App\Values\Link;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<Link, Link>
 */
final class AsLink implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Link
    {
        if ($value === null) {
            return null;
        }
        if (! is_string($value)) {
            throw new InvalidArgumentException("The $key value must be a string.");
        }

        return new Link($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }
        // @phpstan-ignore instanceof.alwaysTrue
        if (! $value instanceof Link) {
            throw new InvalidArgumentException("The $key value given must be of type ".Link::class);
        }

        return (string) $value;
    }
}
