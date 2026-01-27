<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Values\CompanyDomain;
use Illuminate\Database\Eloquent\Factories\Factory;
use ReflectionProperty;

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
            'company_domain' => $this->randomDomain(),
            'company_description' => fake()->sentence(50),
        ];
    }

    private function randomDomain(): CompanyDomain
    {
        $domain = new CompanyDomain(fake()->domainName());
        new ReflectionProperty(CompanyDomain::class, 'exists')->setValue($domain, true);

        return $domain;
    }
}
