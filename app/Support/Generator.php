<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\JobCompensationCurrency;
use App\Enums\JobCompensationType;
use App\Values\Address;
use App\Values\CompanyDomain;
use App\Values\JobCompensation;
use App\Values\Link;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Illuminate\Support\Str;

final class Generator
{
    /**
     * Generate a random address.
     */
    public static function randomAddress(): Address
    {
        $sanitize = fn (string $string) => Str::of($string)
            ->replace('’', "'")
            ->replace('&', 'and')
            ->replace('St.', 'Saint')
            ->replace('U.S.', 'United States')
            ->replaceMatches('/[\p{P}]/u', '')
            ->replaceMatches('/\(.+\)/', '')
            ->trim()
            ->toString();

        // Randomly select a country
        $countryCode = fake()->countryCode();
        $country = new CountryRepository()->get($countryCode)->getName();

        // Randomly select an administrative area for the country
        $administrativeAreas = new SubdivisionRepository()->getAll([$countryCode]);
        $administrativeArea = match ($administrativeAreas !== []) {
            true => collect($administrativeAreas)->random()->getName(),
            false => null,
        };

        // Make up a street address
        $street = sprintf(
            '%d %s %s',
            fake()->numberBetween(1, 10000),
            fake()->streetName(),
            fake()->streetSuffix()
        );

        // Make up a postal code
        $postalCode = fake()->regexify('[A-Z0-9]{3}(-| )[A-Z0-9]{3}');

        return new Address(
            $sanitize($country),
            $administrativeArea ? $sanitize($administrativeArea) : null,
            $sanitize(fake()->city()),
            $sanitize($street),
            $postalCode
        );
    }

    /**
     * Generate a realistic job compensation
     *
     * @param  'low'|'medium'|'high'  $level
     */
    public static function randomJobCompensation(string $level = 'medium', ?JobCompensationType $type = null, ?JobCompensationCurrency $currency = null): JobCompensation
    {
        $type ??= collect(JobCompensationType::cases())->random();
        $currency ??= collect(JobCompensationCurrency::cases())->random();

        [$minimum, $maximum] = match ($level) {
            'low' => [random_int(35000, 40000), random_int(45000, 50000)],
            'medium' => [random_int(51000, 60000), random_int(80000, 90000)],
            'high' => [random_int(91000, 100000), random_int(100000, 200000)],
        };

        $getHourlyPay = fn (int $salary): int => $salary / 52 / 40 |> round(...) |> intval(...);

        return new JobCompensation(
            $type->isHourly() ? $getHourlyPay($minimum) : $minimum,
            $type->isHourly() ? $getHourlyPay($maximum) : $maximum,
            $currency,
            $type
        );
    }

    /**
     * Generate a random domain
     */
    public static function randomDomain(): CompanyDomain
    {
        return new CompanyDomain(fake()->domainName());
    }

    /**
     * Generate a random link.
     */
    public static function randomLink(): Link
    {
        return new Link(fake()->imageUrl());
    }
}
