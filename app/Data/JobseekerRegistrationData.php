<?php

declare(strict_types=1);

namespace App\Data;

use App\Http\Requests\JobseekerRegistrationRequest;

final readonly class JobseekerRegistrationData
{
    public function __construct(
        public string $email,
        public string $password,
        public string $firstname,
        public string $lastname,
        public string $resumeLink,
        public string $country,
        public string $municipality,
        public ?string $administrativeArea = null,
        public ?string $street = null,
        public ?string $postalCode = null,
        public ?string $phoneNumber = null,
    ) {}

    public static function createFromRequest(JobseekerRegistrationRequest $request): self
    {
        return new self(
            $request->safe()->input('email'),
            $request->safe()->input('password'),
            $request->safe()->input('firstname'),
            $request->safe()->input('lastname'),
            $request->safe()->input('resumeLink'),
            $request->safe()->input('address.country'),
            $request->safe()->input('address.municipality'),
            $request->safe()->input('address.administrativeArea'),
            $request->safe()->input('address.street'),
            $request->safe()->input('address.postalCode'),
            $request->safe()->input('phoneNumber'),
        );
    }
}
