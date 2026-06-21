<?php

declare(strict_types=1);

namespace App\Data;

final class JobCompensationData
{
    /**
     * @param  non-empty-string  $currency
     * @param  non-empty-string  $type
     */
    public function __construct(
        public int $minimum,
        public int $maximum,
        public string $currency,
        public string $type,
    ) {}
}
