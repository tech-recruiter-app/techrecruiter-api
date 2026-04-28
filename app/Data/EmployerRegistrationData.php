<?php

declare(strict_types=1);

namespace App\Data;

use App\Http\Requests\EmployerRegistrationRequest;

final readonly class EmployerRegistrationData
{
    public function __construct(
        public string $email,
        public string $password,
        public string $companyName,
        public string $companyDomain,
        public string $companyDescription,
        public string $country,
        public string $municipality,
        public ?string $administrativeArea = null,
        public ?string $street = null,
        public ?string $postalCode = null,
    ) {}

    public static function createFromRequest(EmployerRegistrationRequest $request): self
    {
        return new self(
            $request->safe()->input('email'),
            $request->safe()->input('password'),
            $request->safe()->input('companyName'),
            $request->safe()->input('companyDomain'),
            $request->safe()->input('companyDescription'),
            $request->safe()->input('address.country'),
            $request->safe()->input('address.municipality'),
            $request->safe()->input('address.administrativeArea'),
            $request->safe()->input('address.street'),
            $request->safe()->input('address.postalCode')
        );
    }
}
