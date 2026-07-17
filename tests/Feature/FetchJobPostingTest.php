<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\JobType;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FetchJobPostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_fetch_jobs_filtered_by_job_title_or_company(): void
    {
        // ARRANGE
        $title = JobPosting::active()->pluck('job_title')->random();

        // ACT
        $response = $this->get(route('job-postings.index', ['query' => $title]));

        // ASSERT
        /** @var array<int, array<string, mixed>> $postings */
        $postings = $response->json('data');

        $this->assertTrue(
            array_all($postings, fn ($posting): bool => strcasecmp((string) $posting['job']['title'], (string) $title) === 0),
            "No job postings were returned for the query '{$title}'."
        );
    }

    public function test_user_can_fetch_jobs_filtered_by_location(): void
    {
        // ARRANGE
        $jobPosting = JobPosting::active()->cursor()->random();
        $location = sprintf('%s, %s', $jobPosting->job->address->municipality, $jobPosting->job->address->country);

        // ACT
        $response = $this->get(route('job-postings.index', ['location' => $location]));

        // ASSERT
        /** @var array<int, array<string, mixed>> $postings */
        $postings = $response->json('data');

        $this->assertTrue(
            array_all(
                $postings,
                fn ($posting): bool => strcasecmp((string) $posting['job']['address']['municipality'], $jobPosting->job->address->municipality) === 0 && strcasecmp((string) $posting['job']['address']['country'], $jobPosting->job->address->country) === 0
            ),
            "Not every job posting have the expected location '{$location}'."
        );
    }

    public function test_user_can_fetch_jobs_filtered_by_type(): void
    {
        // ARRANGE
        $jobType = collect(JobType::cases())->random()->value;

        // ACT
        $response = $this->get(route('job-postings.index', ['type' => $jobType]));

        // ASSERT
        /** @var array<int, array<string, mixed>> $postings */
        $postings = $response->json('data');

        $this->assertTrue(
            array_all($postings, fn ($posting): bool => strcasecmp((string) $posting['job']['type'], $jobType) === 0),
            "Not every job posting have the expected type '{$jobType}'."
        );
    }

    public function test_user_can_fetch_jobs_filtered_by_recency(): void
    {
        // ARRANGE
        $recency = collect([1, 3, 7, 14])->random();
        JobPosting::active()->toBase()->inRandomOrder()->limit(10)->update([
            'created_at' => now()->subDays($recency),
        ]);

        // ACT
        $response = $this->get(route('job-postings.index', ['recency' => $recency]));

        // ASSERT
        /** @var array<int, array<string, mixed>> $postings */
        $postings = $response->json('data');

        $this->assertTrue(
            array_all(
                $postings,
                fn ($posting): bool => (int) (round(now()->diffInDays($posting['created_at'], true))) === $recency
            ),
            "Not every job posting was created within the last {$recency} days."
        );
    }
}
