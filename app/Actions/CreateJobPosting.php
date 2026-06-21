<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\NewJobPostingData;
use App\Enums\JobCompensationCurrency;
use App\Enums\JobCompensationType;
use App\Enums\JobPostingStatus;
use App\Models\JobPosting;
use App\Values\Address;
use App\Values\EducationalQualification;
use App\Values\Job;
use App\Values\JobCompensation;
use App\Values\JobDescription;
use App\Values\Link;
use App\Values\TechStack;

final readonly class CreateJobPosting
{
    /**
     * Execute the action.
     *
     * @param  NewJobPostingData  $jobPostingDto  Data transfer object containing the details of the job posting to be created
     * @return JobPosting The newly created job posting instance
     */
    public function handle(NewJobPostingData $jobPostingDto): JobPosting
    {
        $job = new Job(
            $jobPostingDto->jobTitle,
            $jobPostingDto->jobType,
            new Address(
                $jobPostingDto->jobLocation->country,
                $jobPostingDto->jobLocation->administrativeArea,
                $jobPostingDto->jobLocation->municipality,
                $jobPostingDto->jobLocation->street,
                $jobPostingDto->jobLocation->postalCode
            ),
            new JobCompensation(
                $jobPostingDto->jobCompensation->minimum,
                $jobPostingDto->jobCompensation->maximum,
                JobCompensationCurrency::from($jobPostingDto->jobCompensation->currency),
                JobCompensationType::from($jobPostingDto->jobCompensation->type)
            ),
            new TechStack($jobPostingDto->jobStack),
            new JobDescription(
                $jobPostingDto->jobResponsibilities,
                $jobPostingDto->jobRequirements,
                $jobPostingDto->jobBenefits,
                isset($jobPostingDto->educationalRequirement)
                    ? new EducationalQualification(
                        $jobPostingDto->educationalRequirement['degree'],
                        $jobPostingDto->educationalRequirement['field']
                    )
                    : null,
            ),
            $jobPostingDto->jobStartDate
        );

        // Create the job posting
        $jobPosting = new JobPosting(['job' => $job]);
        $jobPosting->status = JobPostingStatus::from($jobPostingDto->status);
        $jobPosting->employer()->associate($jobPostingDto->employerId);
        if (isset($jobPostingDto->link)) {
            $jobPosting->link = new Link($jobPostingDto->link);
        }

        // Persist the job posting
        $jobPosting->saveOrFail();

        return $jobPosting;
    }
}
