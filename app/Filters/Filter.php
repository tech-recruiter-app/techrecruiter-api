<?php

declare(strict_types=1);

namespace App\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract readonly class Filter
{
    /**
     * @param  mixed  $value  The value to filter by
     */
    public function __construct(
        protected mixed $value,
    ) {}

    /**
     * Apply the specific filter logic to the query builder.
     *
     * @param  Builder<Model>  $query  The query builder to apply the filter to
     * @return Builder<Model> The modified query builder after applying the filter
     */
    abstract protected function filter(Builder $query): Builder;

    /**
     * Apply the filter to the given query builder.
     *
     * @param  Builder<Model>  $query  The query builder to apply the filter to
     * @param  Closure(Builder<Model>): Builder<Model>  $next  The next filter in the chain
     * @return Builder<Model> The result of the next filter in the chain
     */
    final public function handle(Builder $query, Closure $next): Builder
    {
        if (is_null($this->value)) {
            return $next($query);
        }

        return $next(
            $this->filter($query)
        );
    }
}
