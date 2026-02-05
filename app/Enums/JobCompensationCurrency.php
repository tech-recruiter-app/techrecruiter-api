<?php

declare(strict_types=1);

namespace App\Enums;

enum JobCompensationCurrency: string
{
    case UNITED_STATES_DOLLAR = 'USD';
    case CANADIAN_DOLLAR = 'CAD';

    public function isUSCurrency(): bool
    {
        return $this === self::UNITED_STATES_DOLLAR;
    }

    public function isCanadianCurrency(): bool
    {
        return $this === self::CANADIAN_DOLLAR;
    }
}
