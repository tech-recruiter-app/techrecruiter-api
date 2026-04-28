<?php

declare(strict_types=1);

namespace Database\Factories\Traits;

use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Illuminate\Support\Str;
use ReflectionClass;

trait GeneratesRandomAddresses
{
    /**
     * @return array{country: string, administrative_area: string|null, municipality: string, street: string, postal_code: string}
     */
    protected function randomAddress(): array
    {
        $country = $this->randomCountry();
        $locale = $this->getLocale($country['code']);

        return [
            'country' => $country['name'],
            'administrative_area' => $this->randomAdministrativeArea($country['code']),
            'municipality' => fake($locale)->city(),
            'street' => $this->randomStreet($locale),
            'postal_code' => fake($locale)->postcode(),
        ];
    }

    /**
     * @return array{code: string, name: string}
     */
    protected function randomCountry(): array
    {
        $code = fake()->countryCode();
        $name = Str::of(new CountryRepository()->get($code)->getName())
            ->replaceMatches(['/\./', '/&/', '/\(.+\)/'], ['', 'and', ''])
            ->trim()
            ->toString();

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
        return sprintf(
            '%d %s %s',
            fake($locale)->numberBetween(1, 10000),
            fake($locale)->streetName(),
            fake($locale)->streetSuffix()
        );
    }

    protected function getLocale(string $countryCode): ?string
    {
        /** @var string */
        $fakerFactoryPath = new ReflectionClass(\Faker\Factory::class)->getFileName();
        $providerDirectory = dirname($fakerFactoryPath).'/Provider';

        /** @var string[] */
        $providerDirectoryContent = scandir($providerDirectory);
        $locales = collect($providerDirectoryContent)
            ->filter(fn ($item): bool => is_dir($providerDirectory.'/'.$item) && ! in_array($item, ['.', '..']))
            ->all();

        return array_find($locales, fn ($locale): bool => locale_get_region($locale) === $countryCode);
    }
}
