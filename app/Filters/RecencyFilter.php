<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final readonly class RecencyFilter extends Filter
{
    protected function filter(Builder $query): Builder
    {
        assert(is_int($this->value) && in_array($this->value, [1, 3, 7, 14], true));

        return match ($this->value) {
            1 => $query->where('created_at', '>=', now()->subDay()),
            3 => $query->where('created_at', '>=', now()->subDays(3)),
            7 => $query->where('created_at', '>=', now()->subWeek()),
            14 => $query->where('created_at', '>=', now()->subWeeks(2)),
        };
    }
}
