<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Values\Email;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class UserFactory extends Factory
{
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

    /**
     * @return array{address_country: string, address_administrative_area: string|null, address_municipality: string, address_street: string|null, address_postal_code: string|null}
     */
    protected function randomAddress(): array
    {
        $country = $this->randomCountry();
        $locale = $this->getLocale($country['code']);

        return [
            'address_country' => $country['name'],
            'address_administrative_area' => $this->randomAdministrativeArea($country['code']),
            'address_municipality' => fake($locale)->city(),
            'address_street' => $this->randomStreet($locale),
            'address_postal_code' => fake($locale)->optional(0.25)->postcode(),
        ];
    }

    /**
     * @return array{code: string, name: string}
     */
    protected function randomCountry(): array
    {
        $code = fake()->countryCode();
        $name = new CountryRepository()->get($code)->getName()
        |> (fn ($name) => (string) preg_replace('/\./', '', (string) $name))
        |> (fn ($name) => (string) preg_replace('/&/', 'and', (string) $name))
        |> trim(...);

        return ['code' => $code, 'name' => $name];
    }

    protected function randomAdministrativeArea(string $countryCode): ?string
    {
        $administrativeAreas = new SubdivisionRepository()->getAll([$countryCode]);

        return match (count($administrativeAreas) > 0) {
            true => ($administrativeAreas[array_rand($administrativeAreas)])->getName(),
            false => null,
        };
    }

    protected function randomStreet(?string $locale): string
    {
        return fake($locale)->streetAddress()
        |> (fn ($street) => (string) preg_replace('/[#\.]/', '', (string) $street))
        |> (fn ($street) => (string) preg_replace('/(Apt|Suite|Block)\s\d+/i', '', (string) $street))
        |> trim(...);
    }

    protected function getLocale(string $countryCode): ?string
    {
        if (($locales = resourcebundle_locales('')) === false) {
            return null;
        }

        /** @var string[] $locales */
        return array_find(
            $locales,
            fn ($locale): bool => locale_get_region($locale) === $countryCode
        );
    }
}
