<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final readonly class JobLocationFilter extends Filter
{
    /**
     * @param  string  $country  The country to filter job postings by.
     */
    public function __construct(
        mixed $value,
        private string $country,
    ) {
        parent::__construct($value);
    }

    protected function filter(Builder $query): Builder
    {
        assert(is_string($this->value));
        assert($query->getModel() instanceof \App\Models\JobPosting);

        preg_match('/^([^,]+),\s?([^,]+)$/', $this->value, $matches);
        $city = mb_trim($matches[1]);
        $region = mb_trim($matches[2]);

        return $query
            ->where('job_address_country', $this->country)
            ->where('job_address_administrative_area', $region)
            ->where('job_address_municipality', $city);
    }
}
