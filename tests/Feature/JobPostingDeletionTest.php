<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class JobPostingDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_employer_can_delete_their_job_posting(): void
    {
        // ARRANGE
        $employer = User::employers()->scopes('verified')->get()->random();
        $jobPostingId = JobPosting::query()
            ->where('employer_id', $employer->id)
            ->pluck('id')
            ->random();

        // ACT
        $response = $this->actingAs($employer)->delete(
            route('job-postings.delete', ['jobPosting' => $jobPostingId])
        );

        // ASSERT
        $response->assertNoContent();
        $this->assertSoftDeleted(JobPosting::class, ['id' => $jobPostingId]);
    }

    public function test_an_employer_cannot_delete_a_job_posting_not_created_by_them(): void
    {
        // ARRANGE
        $employer = User::employers()->scopes('verified')->get()->random();
        $jobPostingId = JobPosting::query()
            ->whereNot('employer_id', $employer->id)
            ->pluck('id')
            ->random();

        // ACT
        $response = $this->actingAs($employer)->delete(
            route('job-postings.delete', ['jobPosting' => $jobPostingId])
        );

        // ASSERT
        $response->assertForbidden();
    }
}
