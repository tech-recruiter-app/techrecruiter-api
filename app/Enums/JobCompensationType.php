<?php

declare(strict_types=1);

namespace App\Enums;

enum JobCompensationType: string
{
    case HOURLY = 'hourly';
    case SALARY = 'salary';

    public function isHourly(): bool
    {
        return $this === self::HOURLY;
    }

    public function isSalary(): bool
    {
        return $this === self::SALARY;
    }
}
