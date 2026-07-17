<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\JobType;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Iterator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;
use Tests\TestCase;

final class FetchJobPostingTest extends TestCase
{
    use RefreshDatabase;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $position = Location::get();
        if (! $position instanceof Position) {
            throw new RuntimeException('Unable to determine the current location for testing.');
        }

        JobPosting::query()->update([
            'job_address_municipality' => $position->cityName,
            'job_address_administrative_area' => $position->regionName,
            'job_address_country' => $position->countryName,
        ]);
    }

    /**
     * @return Iterator<string, array<string>>
     */
    public static function jobTypeProvider(): Iterator
    {
        foreach (JobType::cases() as $jobType) {
            yield "{$jobType->label()} jobs" => [$jobType->value];
        }
    }

    /**
     * @return Iterator<string, array<int>>
     */
    public static function recencyProvider(): Iterator
    {
        foreach ([1, 3, 7, 14, 30] as $recency) {
            yield "Posted within the last {$recency} days" => [$recency];
        }
    }

    public function test_user_can_search_jobs_postings_by_title(): void
    {
        // ARRANGE
        $title = str((string) JobPosting::active()->cursor()->random()->job->title)
            ->remove(' and ')
            ->explode(' ')
            ->random();

        // ACT
        $response = $this->get(route('job-postings.index', ['query' => $title]));
        $jobPostings = $response->json('data');

        // ASSERT
        $this->assertIsArray(
            $jobPostings,
            sprintf("No job postings were found for the search query '%s'.", $title)
        );
    }

    public function test_user_can_search_jobs_postings_by_company(): void
    {
        // ARRANGE
        $company = JobPosting::active()->cursor()->random()->employer->profile->company_name;

        // ACT
        $response = $this->get(route('job-postings.index', ['query' => $company]));
        $jobPostings = $response->json('data');

        // ASSERT
        $this->assertIsArray(
            $jobPostings,
            sprintf("No job postings were found for the company '%s'.", $company)
        );
        $this->assertTrue(
            array_all(
                $jobPostings,
                fn ($jobPosting): bool => strcasecmp(
                    (string) $jobPosting['author']['profile']['company_name'],
                    $company
                ) === 0
            ),
            "Some job postings were not published by '{$company}'."
        );
    }

    public function test_user_can_filter_jobs_postings_by_location(): void
    {
        // ARRANGE
        $address = JobPosting::active()->cursor()->random()->job->address;
        $municipality = $address->municipality;
        $region = $address->administrativeArea;
        $location = when(isset($region), sprintf('%s, %s', $municipality, $region), $municipality);

        // ACT
        $response = $this->get(route('job-postings.index', ['location' => $location]));
        $jobPostings = $response->json('data');

        // ASSERT
        $this->assertIsArray(
            $jobPostings,
            sprintf("No job postings were found for the location '%s'.", $location)
        );
        $this->assertTrue(
            array_all(
                $jobPostings,
                fn ($jobPosting): bool => strcasecmp(
                    (string) $jobPosting['job']['address']['municipality'],
                    $municipality
                ) === 0
            ),
            "Some job postings do not have the expected location '{$location}'."
        );
    }

    #[DataProvider('jobTypeProvider')]
    public function test_user_can_filter_jobs_postings_by_type(string $jobType): void
    {
        // ACT
        $response = $this->get(route('job-postings.index', ['type' => $jobType]));
        $jobPostings = $response->json('data');

        // ASSERT
        $this->assertIsArray(
            $jobPostings,
            sprintf("No job postings were found for the job type '%s'.", $jobType)
        );
        $this->assertTrue(
            array_all(
                $jobPostings,
                fn ($jobPosting): bool => strcasecmp((string) $jobPosting['job']['type'], $jobType) === 0
            ),
            "Some job postings have a different type than '{$jobType}'."
        );
    }

    #[DataProvider('recencyProvider')]
    public function test_user_can_filter_jobs_postings_by_recency(int $recency): void
    {
        // ARRANGE
        JobPosting::active()->toBase()->inRandomOrder()->limit(10)->update([
            'created_at' => now()->subDays($recency),
        ]);

        // ACT
        $response = $this->get(route('job-postings.index', ['recency' => $recency]));
        $jobPostings = $response->json('data');

        // ASSERT
        $this->assertIsArray(
            $jobPostings,
            sprintf('No job postings were found that were created within the last %d days.', $recency)
        );
        $this->assertTrue(
            array_all(
                $jobPostings,
                fn ($jobPosting): bool => round(now()->diffInDays($jobPosting['created_at'], true)) <= $recency
            ),
            "Some job postings were not created within the last {$recency} days."
        );
    }
}
