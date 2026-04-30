<?php

declare(strict_types=1);

namespace App\Enums;

enum UserType: string
{
    case Employer = 'employer';
    case Jobseeker = 'jobseeker';

    public function isEmployer(): bool
    {
        return $this === self::Employer;
    }

    public function isJobseeker(): bool
    {
        return $this === self::Jobseeker;
    }
}
