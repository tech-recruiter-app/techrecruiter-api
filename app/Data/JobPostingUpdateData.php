<?php

declare(strict_types=1);

namespace App\Data;

final class JobPostingUpdateData
{
    /**
     * @param  non-empty-string|null  $jobTitle
     * @param  non-empty-string|null  $jobType
     * @param  array<non-empty-string>|null  $jobStack
     * @param  array<non-empty-string>|null  $jobResponsibilities
     * @param  array<non-empty-string>|null  $jobRequirements
     * @param  array<non-empty-string>|null  $jobBenefits
     * @param  array{degree: string, field: string}|null  $educationalRequirement
     * @param  non-empty-string|null  $jobStartDate
     * @param  non-empty-string|null  $link
     */
    public function __construct(
        public ?string $jobTitle = null,
        public ?string $jobType = null,
        public ?AddressData $jobLocation = null,
        public ?JobCompensationData $jobCompensation = null,
        public ?array $jobStack = null,
        public ?array $jobResponsibilities = null,
        public ?array $jobRequirements = null,
        public ?array $jobBenefits = null,
        public ?array $educationalRequirement = null,
        public ?string $jobStartDate = null,
        public ?string $link = null,
        public ?string $status = null,
    ) {}
}
