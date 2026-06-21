<?php

declare(strict_types=1);

namespace App\Data;

final readonly class NewJobPostingData
{
    /**
     * @param  non-empty-string  $jobTitle
     * @param  non-empty-string  $jobType
     * @param  array<non-empty-string>  $jobStack
     * @param  array<non-empty-string>  $jobResponsibilities
     * @param  array<non-empty-string>  $jobRequirements
     * @param  array<non-empty-string>  $jobBenefits
     * @param  array{degree: string, field: string}|null  $educationalRequirement
     * @param  non-empty-string|null  $jobStartDate
     * @param  non-empty-string|null  $link
     */
    public function __construct(
        public string $jobTitle,
        public string $jobType,
        public AddressData $jobLocation,
        public JobCompensationData $jobCompensation,
        public array $jobStack,
        public array $jobResponsibilities,
        public array $jobRequirements,
        public array $jobBenefits,
        public string $employerId,
        public string $status,
        public ?array $educationalRequirement = null,
        public ?string $jobStartDate = null,
        public ?string $link = null,
    ) {}
}
