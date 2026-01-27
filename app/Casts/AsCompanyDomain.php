<?php

declare(strict_types=1);

namespace App\Casts;

use App\Values\CompanyDomain;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use LogicException;
use ReflectionProperty;

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

        $companyDomain = new CompanyDomain($value);
        new ReflectionProperty(CompanyDomain::class, 'exists')->setValue($companyDomain, true);

        return $companyDomain;
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

        if (! $value->exists) {
            throw new LogicException('The domain must exist in order to be saved.');
        }

        return (string) $value;
    }
}
