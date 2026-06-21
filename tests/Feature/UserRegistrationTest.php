<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\DomainNameVerifier;
use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use App\Models\User;
use App\Notifications\VerifyEmail;
use App\Support\Generator;
use App\Traits\MocksAddressVerifier;
use App\Traits\MocksLinkVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UserRegistrationTest extends TestCase
{
    use MocksAddressVerifier, MocksLinkVerifier, RefreshDatabase;

    public function test_an_employer_can_sign_up(): void
    {
        // ARRANGE
        Notification::fake();
        $this->mockAddressVerifier();
        $this->mock(DomainNameVerifier::class, function (MockInterface $mock): void {
            /** @var Mockery\Expectation $expectation */
            $expectation = $mock->expects('verify');
            $expectation->atLeast()->once();
        });

        $data = $this->randomUserData('employer');

        // ACT
        $response = $this->post(route('employers.create'), $data);

        // ASSERT
        $response->assertCreated();
        $this->assertDatabaseHas(User::class, ['email' => $data['email']])
            ->assertDatabaseHas(EmployerProfile::class, ['company_name' => $data['companyName']]);
        Notification::assertSentTo(
            User::query()->where('email', $data['email'])->firstOrFail(),
            VerifyEmail::class
        );
    }

    public function test_an_authenticated_employer_cannot_signup(): void
    {
        // ARRANGE
        $employer = User::employers()->get()->random();
        $data = $this->randomUserData('employer');

        // ACT
        $response = $this->actingAs($employer)->post(route('employers.create'), $data);

        // ASSERT
        $response->assertForbidden();
    }

    public function test_a_jobseeker_can_sign_up(): void
    {
        // ARRANGE
        Notification::fake();
        $this->mockAddressVerifier();
        $this->mockLinkVerifier();

        $data = $this->randomUserData('jobseeker');

        // ACT
        $response = $this->post(route('jobseekers.create'), $data);

        // ASSERT
        $response->assertCreated();
        $this->assertDatabaseHas(User::class, ['email' => $data['email']])
            ->assertDatabaseHas(JobSeekerProfile::class, [
                'first_name' => $data['firstname'],
                'last_name' => $data['lastname'],
            ]);
        Notification::assertSentTo(
            User::query()->where('email', $data['email'])->firstOrFail(),
            VerifyEmail::class
        );
    }

    public function test_an_authenticated_jobseeker_cannot_signup(): void
    {
        // ARRANGE
        $jobseeker = User::jobseekers()->get()->random();
        $data = $this->randomUserData('jobseeker');

        // ACT
        $response = $this->actingAs($jobseeker)->post(route('jobseekers.create'), $data);

        // ASSERT
        $response->assertForbidden();
    }

    /**
     * @param  'employer'|'jobseeker'  $type
     * @return array<string, mixed>
     */
    private function randomUserData(string $type): array
    {
        $password = Str::random();

        $profile = match ($type) {
            'employer' => [
                'companyName' => fake()->company(),
                'companyDomain' => fake()->domainName(),
                'companyDescription' => fake()->sentence(100),
            ],
            'jobseeker' => [
                'firstname' => fake()->firstName(),
                'lastname' => fake()->lastName(),
                'resumeLink' => fake()->imageUrl(),
            ],
        };

        if ($type === 'jobseeker') {
            $profile['phoneNumber'] = fake()->e164PhoneNumber();
        }

        return [
            'email' => fake()->email(),
            'password' => $password,
            'password_confirmation' => $password,
            'address' => Arr::whereNotNull(Generator::randomAddress()->toArray()),
            ...$profile,
        ];
    }
}
