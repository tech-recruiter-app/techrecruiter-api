<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Support\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployerProfile>
 */
final class EmployerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'company_domain' => Generator::randomDomain(),
            'company_description' => fake()->sentence(50),
        ];
    }
}
