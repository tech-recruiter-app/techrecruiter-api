<?php

declare(strict_types=1);

namespace App\Enums;

enum JobType: string
{
    case FullTime = 'full-time';
    case PartTime = 'part-time';
    case Contract = 'contract';
    case Internship = 'internship';

    /**
     * Determine whether the job position is full-time.
     */
    public function isFullTime(): bool
    {
        return $this === self::FullTime;
    }

    /**
     * Determine whether the job position is part-time.
     */
    public function isPartTime(): bool
    {
        return $this === self::PartTime;
    }

    /**
     * Determine whether the job position is a contract.
     */
    public function isContract(): bool
    {
        return $this === self::Contract;
    }

    /**
     * Determine whether the job position is an internship.
     */
    public function isInternship(): bool
    {
        return $this === self::Internship;
    }

    /**
     * Get the label for the type of the job position.
     */
    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Full-time',
            self::PartTime => 'Part-time',
            self::Contract => 'Contract',
            self::Internship => 'Internship',
        };
    }
}
