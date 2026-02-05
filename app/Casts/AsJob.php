<?php

declare(strict_types=1);

namespace App\Casts;

use App\Traits\RetrievesModelAttributes;
use App\Traits\UnserializesAddresses;
use App\Values\Address;
use App\Values\Job;
use App\Values\JobCompensation;
use App\Values\JobDescription;
use App\Values\JobTitle;
use App\Values\TechStack;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<Job, Job>
 */
final class AsJob implements CastsAttributes
{
    use RetrievesModelAttributes, UnserializesAddresses;

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Job
    {
        return new Job(
            $this->getTitle($attributes),
            $this->getType($attributes),
            $this->getAddress($attributes),
            $this->getCompensation($attributes),
            $this->getStack($attributes),
            $this->getDescription($attributes),
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $value instanceof Job) {
            throw new InvalidArgumentException("The $key value given must be of type ".Job::class);
        }

        return [
            'job_title' => (string) $value->title,
            'job_type' => $value->type->value,
            'job_minimum_compensation' => $value->compensation->minimum,
            'job_maximum_compensation' => $value->compensation->maximum,
            'job_compensation_currency' => $value->compensation->currency->value,
            'job_compensation_type' => $value->compensation->type->value,
            'job_address_country' => $value->address->country,
            'job_address_administrative_area' => $value->address->administrativeArea,
            'job_address_municipality' => $value->address->municipality,
            'job_address_street' => $value->address->street,
            'job_address_postal_code' => $value->address->postalCode,
            'job_stack' => json_encode($value->stack),
            'job_description' => isset($value->description) ? json_encode($value->description) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function getAddress(array $attributes): Address
    {
        return $this->unserializeAddress($attributes, [
            'country' => 'job_address_country',
            'administrative_area' => 'job_address_administrative_area',
            'municipality' => 'job_address_municipality',
            'street' => 'job_address_street',
            'postal_code' => 'job_address_postal_code',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function getCompensation(array $attributes): JobCompensation
    {
        return new JobCompensation(
            $this->getModelAttribute('job_minimum_compensation', $attributes, 'integer'),
            $this->getModelAttribute('job_maximum_compensation', $attributes, 'integer'),
            $this->getModelAttribute('job_compensation_currency', $attributes, 'string'),
            $this->getModelAttribute('job_compensation_type', $attributes, 'string'),
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function getTitle(array $attributes): JobTitle
    {
        return new JobTitle(
            $this->getModelAttribute('job_title', $attributes, 'string')
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function getType(array $attributes): string
    {
        return $this->getModelAttribute('job_type', $attributes, 'string');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function getStack(array $attributes): TechStack
    {
        return TechStack::fromJson(
            $this->getModelAttribute('job_stack', $attributes, 'string')
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function getDescription(array $attributes): ?JobDescription
    {
        $description = $this->getModelAttribute('job_description', $attributes, 'string', true);

        return isset($description) ? JobDescription::fromJson($description) : null;
    }
}
