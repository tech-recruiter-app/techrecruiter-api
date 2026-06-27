<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EmployerProfile;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;

final class JobPostingDeletionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User<EmployerProfile>
     */
    private User $employer;

    private string $jobPostingId;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->employer = User::employers()->scopes('verified')->cursor()->random();
        $this->jobPostingId = JobPosting::query()
            ->where('employer_id', $this->employer->id)
            ->cursor()->random()->id;
    }

    public function test_an_employer_can_delete_their_job_posting(): void
    {
        // ACT
        $response = $this->actingAs($this->employer)->delete(
            route('job-postings.delete', ['jobPosting' => $this->jobPostingId])
        );

        // ASSERT
        $response->assertNoContent();
        $this->assertSoftDeleted(JobPosting::class, ['id' => $this->jobPostingId]);
    }

    public function test_an_employer_cannot_delete_a_job_posting_not_created_by_them(): void
    {
        // ACT
        $response = $this->actingAs($this->employer)->delete(
            route('job-postings.delete', ['jobPosting' => $this->jobPostingId])
        );

        // ASSERT
        $response->assertForbidden();
    }
}
