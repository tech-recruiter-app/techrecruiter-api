<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AddressVerifier;
use App\Contracts\Geocoder;
use App\Enums\LocationType;
use App\Exceptions\Domain\AddressVerificationException;
use App\Values\Address;
use CommerceGuys\Addressing\Address as CommerceGuysAddress;
use CommerceGuys\Addressing\AddressFormat\FieldOverride;
use CommerceGuys\Addressing\AddressFormat\FieldOverrides;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Country\CountryRepositoryInterface;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraint;
use LogicException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DefaultAddressVerifier implements AddressVerifier
{
    private readonly ValidatorInterface $validator;

    public function __construct(
        private ?Geocoder $geocoder = null,
        ?ValidatorInterface $validator = null,
        private readonly CountryRepositoryInterface $countryRepository = new CountryRepository,
        private readonly SubdivisionRepositoryInterface $subdivisionRepository = new SubdivisionRepository,
    ) {
        $this->validator = $validator ?? Validation::createValidator();
    }

    public function setGeocoder(Geocoder $geocoder): self
    {
        $this->geocoder = $geocoder;

        return $this;
    }

    public function verify(Address $address): void
    {
        if (! isset($this->geocoder)) {
            throw new LogicException('No geocoder was provided.');
        }

        // Verify that country provided exists
        $countryCode = $this->getCountryCode($address->country);
        if (is_null($countryCode)) {
            throw new AddressVerificationException("The country [{$address->country}] does not exist.");
        }

        // Verify that administrative area provided exists
        if (filled($address->administrativeArea)) {
            $administrativeAreaCode = $this->getAdministrativeAreaCode($address->administrativeArea, $countryCode);
            if (is_null($administrativeAreaCode)) {
                throw new AddressVerificationException("{$address->administrativeArea} is not an administrative subdivision of {$address->country}.");
            }
        }

        // Geocode the address for further validation
        $result = $this->geocoder->geocode($address);

        // Verify that municipality provided exists
        if (! $result->municipality_exact_match) {
            $area = filled($address->administrativeArea) ? "{$address->administrativeArea}, {$address->country}" : $address->country;
            throw new AddressVerificationException("{$address->municipality} is not a municipality within $area.");
        }

        // Verify that street address provided exists (If provided)
        if (filled($address->street) && ! ($result->street_address_exact_match && in_array($result->locationType, [LocationType::Street, LocationType::Building]))) {
            $area = filled($address->administrativeArea) ? "{$address->municipality}, {$address->administrativeArea}, {$address->country}" : "{$address->municipality}, {$address->country}";
            throw new AddressVerificationException("[{$address->street}] is not a street address within $area.");
        }

        // Verify that postal code provided exists (If provided)
        if (filled($address->postalCode)) {
            $violations = $this->validator->validate(
                new CommerceGuysAddress($countryCode, $administrativeAreaCode ?? '', postalCode: $address->postalCode),
                $this->getAddressConstraintForPostalCodeValidation()
            );
            if ($violations->count() > 0) {
                throw new AddressVerificationException("The postal code [$address->postalCode] is invalid.");
            }
        }
    }

    private function getCountryCode(string $country): ?string
    {
        /** @var array<string, string> $valid_countries */
        $valid_countries = $this->countryRepository->getList();

        return array_find_key(
            $valid_countries,
            function (string $valid_country, string $countryCode) use ($country): bool {
                $country = mb_strtolower($country);

                return mb_strtolower($countryCode) === $country || mb_strtolower($valid_country) === $country;
            }
        );
    }

    private function getAdministrativeAreaCode(string $administrativeArea, string $countryCode): ?string
    {
        /** @var array<string, string> $administrativeAreas */
        $administrativeAreas = $this->subdivisionRepository->getList([$countryCode]);

        return array_find_key(
            $administrativeAreas,
            function (string $valid_administrative_area, string $administrativeAreaCode) use ($administrativeArea): bool {
                $administrativeArea = mb_strtolower($administrativeArea);

                return mb_strtolower($administrativeAreaCode) === $administrativeArea || mb_strtolower($valid_administrative_area) === $administrativeArea;
            }
        );
    }

    private function getAddressConstraintForPostalCodeValidation(): AddressFormatConstraint
    {
        $constraint = new AddressFormatConstraint;
        $constraint->fieldOverrides = new FieldOverrides([
            'locality' => FieldOverride::HIDDEN,
            'dependentLocality' => FieldOverride::HIDDEN,
            'organization' => FieldOverride::HIDDEN,
            'givenName' => FieldOverride::HIDDEN,
            'additionalName' => FieldOverride::HIDDEN,
            'familyName' => FieldOverride::HIDDEN,
            'addressLine1' => FieldOverride::HIDDEN,
            'addressLine2' => FieldOverride::HIDDEN,
            'addressLine3' => FieldOverride::HIDDEN,
        ]);

        return $constraint;
    }
}
