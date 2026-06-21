<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\JobPostingStatus;
use App\Enums\JobType;
use App\Models\JobPosting;
use App\Models\User;
use App\Support\Generator;
use App\Traits\MocksAddressVerifier;
use App\Traits\MocksLinkVerifier;
use App\Values\EducationalQualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class JobPostingUpdateTest extends TestCase
{
    use MocksAddressVerifier, MocksLinkVerifier, RefreshDatabase;

    /**
     * @return Iterator<string, array{string, mixed}>
     */
    public static function provideJobPostingNewAttributes(): Iterator
    {
        $getJobTitle = function (): string {
            do {
                $jobTitle = fake()->jobTitle();
            } while (mb_strlen($jobTitle) < 3);

            return $jobTitle;
        };

        yield 'New job title' => ['job_title', $getJobTitle()];
        yield 'New job type' => ['job_type', collect(JobType::cases())->map(fn (JobType $type) => $type->value)->random()];
        yield 'New job location' => ['job_location', Arr::whereNotNull(Generator::randomAddress()->toArray())];
        yield 'New job compensation' => ['job_compensation', Generator::randomJobCompensation()->toArray()];
        yield 'New job technology stack' => ['job_stack', ['PHP', 'Python', 'Rust']];
        yield 'New job responsibilities' => ['job_responsibilities', [
            'Develop and maintain web applications',
            'Collaborate with cross-functional teams',
        ]];
        yield 'New job requirements' => ['job_requirements', [
            '5+ years of experience in software development',
            'Proficiency in PHP and Laravel',
        ]];
        yield 'New job benefits' => ['job_benefits', [
            'Health insurance',
            '401(k) matching',
        ]];
        yield 'New educational requirement' => ['job_educational_requirement', ['degree' => 'Master', 'field' => 'Computer science']];
        yield 'New job posting status' => ['status', collect(JobPostingStatus::cases())->map(fn (JobPostingStatus $status) => $status->value)->random()];
        yield 'New job posting link' => ['link', fake()->imageUrl()];
        yield 'New job start date' => ['job_starts_on', fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d')];
    }

    /**
     * @param  string|array<int|string, mixed>  $newAttributeValue
     */
    #[DataProvider('provideJobPostingNewAttributes')]
    public function test_an_employer_can_update_their_job_posting(string $attributeName, string|array $newAttributeValue): void
    {
        // ARRANGE
        if ($attributeName === 'link') {
            $this->mockLinkVerifier();
        }
        if ($attributeName === 'job_location') {
            $this->mockAddressVerifier();
        }
        $employer = User::employers()->scopes('verified')->get()->random();
        $jobPosting = JobPosting::query()
            ->where('employer_id', $employer->id)
            ->unless(is_array($newAttributeValue), function ($query) use ($attributeName, $newAttributeValue): void {
                $query->whereNot($attributeName, $newAttributeValue);
            })
            ->firstOrFail();

        // ACT
        $response = $this->actingAs($employer)->patch(
            route('job-postings.update', ['jobPosting' => $jobPosting->id]),
            [$attributeName => $newAttributeValue]
        );

        // ASSERT
        if (is_array($newAttributeValue)) {
            $expectedDatabaseValues = match ($attributeName) {
                'job_location' => [
                    'job_address_country' => $newAttributeValue['country'],
                    'job_address_municipality' => $newAttributeValue['municipality'],
                ],
                'job_compensation' => [
                    'job_compensation_type' => $newAttributeValue['type'],
                    'job_minimum_compensation' => $newAttributeValue['minimum'],
                    'job_maximum_compensation' => $newAttributeValue['maximum'],
                    'job_compensation_currency' => $newAttributeValue['currency'],
                ],
                'job_stack' => ['job_stack' => json_encode(array_map(strtolower(...), $newAttributeValue))],
                'job_responsibilities' => [
                    'job_description' => $jobPosting->job->description
                        ?->withResponsibilities($newAttributeValue) |> json_encode(...),
                ],
                'job_requirements' => [
                    'job_description' => $jobPosting->job->description
                        ?->withRequirements($newAttributeValue) |> json_encode(...),
                ],
                'job_benefits' => [
                    'job_description' => $jobPosting->job->description
                        ?->withBenefits($newAttributeValue) |> json_encode(...),
                ],
                'job_educational_requirement' => [
                    'job_description' => $jobPosting->job->description
                        ?->withEducationalQualification(new EducationalQualification($newAttributeValue['degree'], $newAttributeValue['field'])) |> json_encode(...),
                ],
                default => [],
            };
        } else {
            $expectedDatabaseValues = [$attributeName => $newAttributeValue];
        }

        $response->assertOk();
        $this->assertDatabaseHas(JobPosting::class, ['id' => $jobPosting->id, ...$expectedDatabaseValues]);
    }

    public function test_an_employer_cannot_update_a_job_posting_not_created_by_them(): void
    {
        // ARRANGE
        $newJobType = collect(JobType::cases())->random()->value;
        $employer = User::employers()->scopes('verified')->get()->random();
        $jobPostingId = JobPosting::query()
            ->whereNot('employer_id', $employer->id)
            ->whereNot('job_type', $newJobType)
            ->pluck('id')
            ->random();

        // ACT
        $response = $this->actingAs($employer)->patch(
            route('job-postings.update', ['jobPosting' => $jobPostingId]),
            ['job_type' => $newJobType]
        );

        // ASSERT
        $response->assertForbidden();
    }
}
