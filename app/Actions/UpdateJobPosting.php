<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\JobPostingUpdateData;
use App\Enums\JobCompensationCurrency;
use App\Enums\JobCompensationType;
use App\Enums\JobPostingStatus;
use App\Models\JobPosting;
use App\Values\Address;
use App\Values\EducationalQualification;
use App\Values\JobCompensation;
use App\Values\Link;
use App\Values\TechStack;

final class UpdateJobPosting
{
    /**
     * Execute the action.
     *
     * @param  JobPosting  $jobPosting  The job posting instance to be updated
     * @param  JobPostingUpdateData  $dto  Data transfer object containing the details of the job posting to be updated
     * @return JobPosting The updated job posting instance
     */
    public function handle(JobPosting $jobPosting, JobPostingUpdateData $dto): JobPosting
    {
        $job = $jobPosting->job;

        if (isset($dto->jobTitle)) {
            $job = $job->withTitle($dto->jobTitle);
        }
        if (isset($dto->jobType)) {
            $job = $job->withType($dto->jobType);
        }
        if (isset($dto->jobStack)) {
            $job = $job->withStack(new TechStack($dto->jobStack));
        }
        if (isset($dto->jobLocation)) {
            $job = $job->withAddress(new Address(
                $dto->jobLocation->country,
                $dto->jobLocation->administrativeArea,
                $dto->jobLocation->municipality,
                $dto->jobLocation->street,
                $dto->jobLocation->postalCode
            ));
        }
        if (isset($dto->jobCompensation)) {
            $job = $job->withCompensation(new JobCompensation(
                $dto->jobCompensation->minimum,
                $dto->jobCompensation->maximum,
                JobCompensationCurrency::from($dto->jobCompensation->currency),
                JobCompensationType::from($dto->jobCompensation->type)
            ));
        }
        if (isset($dto->jobResponsibilities)) {
            $job = $job->withDescription($job->description?->withResponsibilities($dto->jobResponsibilities));
        }
        if (isset($dto->jobRequirements)) {
            $job = $job->withDescription($job->description?->withRequirements($dto->jobRequirements));
        }
        if (isset($dto->jobBenefits)) {
            $job = $job->withDescription($job->description?->withBenefits($dto->jobBenefits));
        }
        if (isset($dto->educationalRequirement)) {
            $job = $job->withDescription($job->description?->withEducationalQualification(
                new EducationalQualification(
                    $dto->educationalRequirement['degree'],
                    $dto->educationalRequirement['field']
                )
            ));
        }
        if (isset($dto->jobStartDate)) {
            $job = $job->withStartDate($dto->jobStartDate);
        }
        if (isset($dto->link)) {
            $jobPosting->link = new Link($dto->link);
        }
        if (isset($dto->status)) {
            $jobPosting->status = JobPostingStatus::from($dto->status);
        }

        $jobPosting->job = $job;
        $jobPosting->saveOrFail();

        return $jobPosting;
    }
}
