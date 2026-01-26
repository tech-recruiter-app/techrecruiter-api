<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Values\Email;
use Database\Factories\Traits\RandomAddresses;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class UserFactory extends Factory
{
    use RandomAddresses;

    /**
     * The current password being used by the factory.
     */
    private static string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => new Email(fake()->unique()->safeEmail()),
            'email_verified_at' => now(),
            'password' => self::$password ??= Hash::make('password'),
            ...$this->randomAddress(),
            'profile_type' => fake()->randomElement(['Jobseeker', 'Employer']),
            'profile_id' => Str::uuid7()->toString(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is a job seeker.
     */
    public function jobseeker(): static
    {
        return $this->state(fn (array $attributes): array => [
            'profile_type' => 'Jobseeker',
        ]);
    }

    /**
     * Indicate that the user is an employer.
     */
    public function employer(): static
    {
        return $this->state(fn (array $attributes): array => [
            'profile_type' => 'Employer',
        ]);
    }
}
