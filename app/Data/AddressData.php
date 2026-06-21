<?php

declare(strict_types=1);

namespace App\Data;

final class AddressData
{
    /**
     * @param  non-empty-string  $country
     * @param  non-empty-string  $municipality
     * @param  non-empty-string  $street
     * @param  non-empty-string|null  $administrativeArea
     * @param  non-empty-string|null  $postalCode
     */
    public function __construct(
        public string $country,
        public string $municipality,
        public string $street,
        public ?string $administrativeArea = null,
        public ?string $postalCode = null,
    ) {}
}
