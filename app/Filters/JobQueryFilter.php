<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final readonly class JobQueryFilter extends Filter
{
    protected function filter(Builder $query): Builder
    {
        assert(is_string($this->value));

        return $query->where('job_title', 'like', "%{$this->value}%")
            ->orWhereRelation('employer.profile', 'company_name', 'like', "%{$this->value}%");
    }
}
