<?php

declare(strict_types=1);

namespace App\Casts;

use App\Values\CompanyDomain;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<CompanyDomain, CompanyDomain>
 */
final class AsCompanyDomain implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): CompanyDomain
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException("The $key value must be a string.");
        }

        return new CompanyDomain($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (! $value instanceof CompanyDomain) {
            throw new InvalidArgumentException("The $key value given must be of type ".CompanyDomain::class);
        }

        return (string) $value;
    }
}
