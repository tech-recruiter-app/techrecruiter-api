<?php

declare(strict_types=1);

namespace App\Enums;

enum JobApplicationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case REVIEWED = 'reviewed';
    case INTERVIEWING = 'interviewing';
    case SELECTED = 'selected';
    case HIRED = 'hired';
    case DECLINED = 'declined';
    case WITHDRAWN = 'withdrawn';

    /**
     * Determine whether the job application is still being drafted.
     */
    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Determine whether the job application has been submitted.
     */
    public function isSubmitted(): bool
    {
        return $this === self::SUBMITTED;
    }

    /**
     * Determine whether the job application is under review.
     */
    public function isReviewed(): bool
    {
        return $this === self::REVIEWED;
    }

    /**
     * Determine whether the job application is in the interviewing stage.
     */
    public function isInterviewing(): bool
    {
        return $this === self::INTERVIEWING;
    }

    /**
     * Determine whether the job application was selected and job offer sent.
     */
    public function isSelected(): bool
    {
        return $this === self::SELECTED;
    }

    /**
     * Determine whether the job application resulted in a hire.
     */
    public function wasHired(): bool
    {
        return $this === self::HIRED;
    }

    /**
     * Determine whether the job application was declined.
     */
    public function isDeclined(): bool
    {
        return $this === self::DECLINED;
    }

    /**
     * Determine whether the job application was withdrawn by the applicant.
     */
    public function isWithdrawn(): bool
    {
        return $this === self::WITHDRAWN;
    }

    /**
     * Get the label of the application's status.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Applied',
            self::REVIEWED => 'Under Review',
            self::INTERVIEWING => 'Interviewing',
            self::SELECTED => 'Job offer extended',
            self::HIRED => 'Hired',
            self::DECLINED => 'Not Selected',
            self::WITHDRAWN => 'Withdrawn',
        };
    }
}
