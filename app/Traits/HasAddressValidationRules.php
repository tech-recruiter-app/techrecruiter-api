<?php

declare(strict_types=1);

namespace App\Traits;

trait HasAddressValidationRules
{
    /**
     * Get validation rules that apply to addresses.
     *
     * @param  string|null  $parentKey  The parent key under which the address fields are nested in the validated data (e.g., 'job' if validating an address nested under a 'job' key). If null, rules will be generated for top-level address fields.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function addressRules(?string $parentKey = null): array
    {
        $addressKey = $parentKey ? "{$parentKey}.address" : 'address';

        return [
            $addressKey => ['required', 'array:country,administrativeArea,municipality,street,postalCode', 'required_array_keys:country,municipality'],
            "{$addressKey}.country" => ['required', 'string', 'min:2'],
            "{$addressKey}.administrativeArea" => ['sometimes', 'required', 'string', 'min:2'],
            "{$addressKey}.municipality" => ['required', 'string', 'min:3'],
            "{$addressKey}.street" => ['sometimes', 'required', 'string', 'min:4'],
            "{$addressKey}.postalCode" => ['sometimes', 'required', 'string', 'min:3'],
        ];
    }
}
