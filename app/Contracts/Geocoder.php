<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\GeocodingResult;
use App\Values\Address;

interface Geocoder
{
    /**
     * Geocodes an address.
     *
     * @param  Address  $address  The address to geocode
     * @return GeocodingResult The geocoding result
     */
    public function geocode(Address $address): GeocodingResult;
}
