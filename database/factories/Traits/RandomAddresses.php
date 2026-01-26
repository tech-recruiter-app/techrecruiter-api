<?php

declare(strict_types=1);

namespace Database\Factories\Traits;

use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;

trait RandomAddresses
{
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
