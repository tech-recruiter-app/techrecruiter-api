<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmployerProfile;
use App\Models\JobPosting;
use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class JobPostingPolicy
{
    /**
     * Determine whether the user can view the job posting.
     *
     * @param  User<EmployerProfile|JobSeekerProfile>  $user
     */
    public function view(?User $user, JobPosting $jobPosting): Response
    {
        if ($jobPosting->status->isActive() || ($user?->id === $jobPosting->employer->id)) {
            return Response::allow();
        }

        return Response::deny('You cannot view this job posting.');
    }

    /**
     * Determine whether the user can create job postings.
     *
     * @param  User<EmployerProfile|JobSeekerProfile>  $user
     */
    public function create(User $user): Response
    {
        return $user->type->isEmployer() && $user->hasVerifiedEmail() ?
            Response::allow() :
            Response::deny('Only verified employers can add new job postings.');
    }

    /**
     * Determine whether the user can update the job posting.
     *
     * @param  User<EmployerProfile|JobSeekerProfile>  $user
     */
    public function update(User $user, JobPosting $jobPosting): Response
    {
        return $jobPosting->employer->id === $user->id ?
            Response::allow() :
            Response::deny('You cannot update this job posting.');
    }

    /**
     * Determine whether the user can delete the job posting.
     *
     * @param  User<EmployerProfile|JobSeekerProfile>  $user
     */
    public function delete(User $user, JobPosting $jobPosting): Response
    {
        return $jobPosting->employer->id === $user->id ?
            Response::allow() :
            Response::deny('You cannot remove this job posting.');
    }
}
