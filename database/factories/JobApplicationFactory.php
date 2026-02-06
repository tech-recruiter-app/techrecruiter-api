<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\JobApplicationStatus;
use Database\Factories\Traits\RandomLinks;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
final class JobApplicationFactory extends Factory
{
    use RandomLinks;

    public function randomStatus(): string
    {
        $statuses = array_map(fn (JobApplicationStatus $status) => $status->value, JobApplicationStatus::cases());

        return $statuses[array_rand($statuses)];
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'resume_link' => $this->randomLink(),
            'cover_letter_link' => $this->randomLink(),
            'status' => $this->randomStatus(),
        ];
    }

    /**
     * Indicate that the application has the status given.
     */
    public function withStatus(JobApplicationStatus $status): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => $status->value,
        ]);
    }
}
