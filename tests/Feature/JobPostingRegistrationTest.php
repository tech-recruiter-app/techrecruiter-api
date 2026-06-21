<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\JobPostingStatus;
use App\Enums\JobType;
use App\Models\User;
use App\Support\Generator;
use App\Traits\MocksAddressVerifier;
use App\Traits\MocksLinkVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Override;
use Tests\TestCase;

final class JobPostingRegistrationTest extends TestCase
{
    use MocksAddressVerifier, MocksLinkVerifier, RefreshDatabase;

    /**
     * The data for registering a job posting.
     *
     * @var array<string, mixed>
     */
    private array $jobPostingData;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        do {
            $jobTitle = fake()->jobTitle();
        } while (mb_strlen($jobTitle) < 3);

        $this->jobPostingData = [
            'job_title' => $jobTitle,
            'job_type' => collect(JobType::cases())->map(fn (JobType $type) => $type->value)->random(),
            'job_location' => Arr::whereNotNull(Generator::randomAddress()->toArray()),
            'job_compensation' => Generator::randomJobCompensation()->toArray(),
            'job_stack' => ['PHP', 'Laravel', 'Vue.js'],
            'job_responsibilities' => [
                'Develop and maintain web applications',
                'Collaborate with cross-functional teams',
            ],
            'job_requirements' => [
                '5+ years of experience in software development',
                'Proficiency in PHP and Laravel',
            ],
            'job_benefits' => [
                'Health insurance',
                '401(k) matching',
            ],
            'job_educational_requirement' => [
                'degree' => 'Bachelor',
                'field' => 'Computer Science',
            ],
            'status' => collect(JobPostingStatus::cases())->map(fn (JobPostingStatus $status) => $status->value)->random(),
            'link' => fake()->imageUrl(),
            'job_starts_on' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
        ];
    }

    public function test_a_verified_employer_can_add_a_new_job_posting(): void
    {
        // ARRANGE
        $this->mockAddressVerifier();
        $this->mockLinkVerifier();
        $employer = User::employers()->scopes('verified')->get()->random();

        // ACT
        $response = $this->actingAs($employer)->post(route('job-postings.store'), $this->jobPostingData);

        // ASSERT
        $response->assertCreated();
    }

    public function test_an_unverified_employer_cannot_add_a_new_job_posting(): void
    {
        // ARRANGE
        $employer = User::employers()->scopes('unverified')->get()->random();

        // ACT
        $response = $this->actingAs($employer)->post(route('job-postings.store'), $this->jobPostingData);

        // ASSERT
        $response->assertForbidden();
    }

    public function test_a_jobseeker_cannot_add_a_new_job_posting(): void
    {
        // ARRANGE
        $jobseeker = User::jobseekers()->scopes('verified')->get()->random();

        // ACT
        $response = $this->actingAs($jobseeker)->post(route('job-postings.store'), $this->jobPostingData);

        // ASSERT
        $response->assertForbidden();
    }
}
