<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\JobCompensationCurrency;
use App\Enums\JobCompensationType;
use App\Enums\JobPostingStatus;
use App\Enums\JobType;
use Database\Factories\Traits\RandomAddresses;
use Database\Factories\Traits\RandomLinks;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobPosting>
 */
final class JobPostingFactory extends Factory
{
    use RandomAddresses, RandomLinks;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $address = $this->randomAddress();
        $compensation = $this->randomCompensation();

        return [
            'job_title' => fake()->jobTitle(),
            'job_type' => $this->randomJobType(),
            'job_minimum_compensation' => $compensation['minimum'],
            'job_maximum_compensation' => $compensation['maximum'],
            'job_compensation_currency' => $compensation['currency'],
            'job_compensation_type' => $compensation['type'],
            'job_address_country' => $address['country'],
            'job_address_administrative_area' => $address['administrative_area'],
            'job_address_municipality' => $address['municipality'],
            'job_address_street' => $address['street'],
            'job_address_postal_code' => $address['postal_code'],
            'job_stack' => json_encode($this->randomStack()),
            'job_description' => json_encode($this->randomDescription()),
            'status' => $this->randomStatus(),
            'link' => $this->randomLink(),
        ];
    }

    /**
     * Indicate it is a draft job posting.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => JobPostingStatus::DRAFT->value,
        ]);
    }

    /**
     * Indicate that the job posting is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => JobPostingStatus::ACTIVE->value,
        ]);
    }

    /**
     * Indicate that the job posting is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => JobPostingStatus::CLOSED->value,
        ]);
    }

    /**
     * @return array{minimum: int, maximum: int, currency: string, type: string}
     */
    public function randomCompensation(): array
    {
        $compensationType = $this->randomCompensationType();

        [$minimum_compensation, $maximum_compensation] = match ($compensationType) {
            JobCompensationType::HOURLY => [
                random_int(30, 50),
                random_int(70, 100),
            ],
            JobCompensationType::SALARY => [
                random_int(50000, 70000),
                random_int(90000, 120000),
            ],
        };

        return [
            'minimum' => $minimum_compensation,
            'maximum' => $maximum_compensation,
            'currency' => $this->randomCurrency(),
            'type' => $compensationType->value,
        ];
    }

    private function randomJobType(): string
    {
        $types = array_map(fn (JobType $type) => $type->value, JobType::cases());

        return $types[array_rand($types)];
    }

    /**
     * @return string[]
     */
    private function randomStack(): array
    {
        $technologies = ['PHP', 'JavaScript', 'Laravel', 'Vue.js', 'Reactjs', 'MySQL', 'PostgreSQL', 'Next.js'];
        $keys = array_rand($technologies, random_int(3, 5));

        return array_map(fn (int $key): string => $technologies[$key], $keys);
    }

    private function randomStatus(): string
    {
        $statuses = array_map(fn (JobPostingStatus $status) => $status->value, JobPostingStatus::cases());

        return $statuses[array_rand($statuses)];
    }

    private function randomCurrency(): string
    {
        $statuses = array_map(fn (JobCompensationCurrency $status) => $status->value, JobCompensationCurrency::cases());

        return $statuses[array_rand($statuses)];
    }

    private function randomCompensationType(): JobCompensationType
    {
        $types = JobCompensationType::cases();

        return $types[array_rand($types)];
    }

    // @phpstan-ignore missingType.iterableValue
    private function randomDescription(): array
    {
        return [
            'responsibilities' => [
                fake()->sentence(10),
                fake()->sentence(10),
                fake()->sentence(10),
            ],
            'requirements' => [
                fake()->sentence(10),
                fake()->sentence(10),
                fake()->sentence(10),
            ],
            'benefits' => [
                fake()->sentence(10),
                fake()->sentence(10),
                fake()->sentence(10),
            ],
            'education' => "Bachelor's",
            'startDate' => fake()->dateTimeBetween('+2 weeks', '+2 months')->format('Y-m-d'),
        ];
    }
}
