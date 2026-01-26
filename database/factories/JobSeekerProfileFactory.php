<?php

declare(strict_types=1);

namespace Database\Factories;

use Database\Factories\Traits\RandomLinks;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobSeekerProfile>
 */
final class JobSeekerProfileFactory extends Factory
{
    use RandomLinks;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone_number' => fake()->e164PhoneNumber(),
            'resume_link' => $this->randomLink(),
        ];
    }
}
