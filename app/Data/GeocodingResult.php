<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\LocationType;

final readonly class GeocodingResult
{
    /**
     * @param  LocationType  $locationType  Found location type for the request address
     * @param  bool  $municipality_exact_match  Indicates if the geocoder returned an exact match for the municipality
     * @param  null|bool  $street_address_exact_match  Indicates if the geocoder returned an exact match for the street address
     */
    public function __construct(
        public LocationType $locationType,
        public bool $municipality_exact_match = false,
        public ?bool $street_address_exact_match = null,
    ) {}
}
