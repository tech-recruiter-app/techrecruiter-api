<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final readonly class JobTypeFilter extends Filter
{
    protected function filter(Builder $query): Builder
    {
        assert(is_string($this->value));
        assert($query->getModel() instanceof \App\Models\JobPosting);

        return $query->where('job_type', $this->value);
    }
}
