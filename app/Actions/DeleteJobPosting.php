<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\JobApplicationStatus;
use App\Models\JobPosting;
use Illuminate\Support\Facades\DB;

final class DeleteJobPosting
{
    /**
     * Execute the action.
     */
    public function handle(JobPosting $jobPosting): void
    {
        DB::transaction(function () use ($jobPosting): void {
            foreach ($jobPosting->jobApplications as $jobApplication) {
                if (! $jobApplication->status->wasHired()) {
                    $jobApplication->status = JobApplicationStatus::DECLINED;
                    $jobApplication->save();
                }
            }

            $jobPosting->delete();
        });
    }
}
