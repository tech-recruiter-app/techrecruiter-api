<?php

declare(strict_types=1);

namespace App\Enums;

enum JobPostingStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case CLOSED = 'closed';

    /**
     * Determine whether the job posting is a draft.
     */
    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Determine whether the job position is active.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Determine whether the job position is closed.
     */
    public function isClosed(): bool
    {
        return $this === self::CLOSED;
    }

    /**
     * Get the label of the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Active',
            self::CLOSED => 'Closed',
        };
    }
}
