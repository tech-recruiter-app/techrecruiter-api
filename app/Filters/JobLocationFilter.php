<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final readonly class JobLocationFilter extends Filter
{
    protected function filter(Builder $query): Builder
    {
        assert(is_string($this->value));

        if (preg_match('/^([^,]+),\s?([^,]+)$/', $this->value, $matches)) {
            $city = mb_trim($matches[1]);
            $stateOrCountry = mb_trim($matches[2]);

            return $query->where('job_address_municipality', 'like', "%{$city}%")
                ->where('job_address_administrative_area', 'like', "%{$stateOrCountry}%")
                ->orWhere('job_address_country', 'like', "%{$stateOrCountry}%");
        }

        return $query->where('job_address_municipality', 'like', sprintf('%%%s%%', $this->value))
            ->orwhere('job_address_administrative_area', 'like', sprintf('%%%s%%', $this->value))
            ->orWhere('job_address_country', 'like', sprintf('%%%s%%', $this->value));
    }
}
