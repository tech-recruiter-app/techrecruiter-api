<?php

declare(strict_types=1);

namespace App\Casts;

use App\Traits\RetrievesModelAttributes;
use App\Values\Name;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<Name, Name>
 */
final class AsName implements CastsAttributes
{
    use RetrievesModelAttributes;

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Name
    {
        return new Name(
            $this->getModelAttribute('first_name', $attributes, 'string'),
            $this->getModelAttribute('last_name', $attributes, 'string'),
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
