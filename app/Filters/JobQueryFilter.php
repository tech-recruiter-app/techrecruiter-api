<?php

declare(strict_types=1);

namespace App\Filters;

use App\Models\EmployerProfile;
use Illuminate\Database\Eloquent\Builder;

final readonly class JobQueryFilter extends Filter
{
    protected function filter(Builder $query): Builder
    {
        assert(is_string($this->value));
        assert($query->getModel() instanceof \App\Models\JobPosting);

        return $query
            ->whereFullText('job_title', $this->value)
            ->orWhereHas('employer', function (Builder $query): void {
                $query->whereMorphRelation('profile', EmployerProfile::class, 'company_name', $this->value);
            });
    }
}
