<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Validation\Rule;

trait HasAddressValidationRules
{
    /**
     * Get validation rules that apply to addresses.
     *
     * @param  string  $attribute  The base attribute name for the address
     * @param  string|null  $parentKey  The parent key under which the address fields are nested in the validated data (e.g., 'job' if validating an address nested under a 'job' key). If null, rules will be generated for top-level address fields.
     * @param  bool  $optional  Whether the address fields are optional.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function addressRules(string $attribute = 'address', ?string $parentKey = null, bool $optional = false): array
    {
        $addressKey = $parentKey ? "{$parentKey}.{$attribute}" : $attribute;

        return [
            $addressKey => [Rule::when($optional, 'sometimes'), 'required', 'array:country,administrativeArea,municipality,street,postalCode', 'required_array_keys:country,municipality'],
            "{$addressKey}.country" => [sprintf('required_with:%s', $addressKey), 'string', 'min:2'],
            "{$addressKey}.administrativeArea" => ['sometimes', sprintf('required_with:%s', $addressKey), 'string', 'min:2'],
            "{$addressKey}.municipality" => [sprintf('required_with:%s', $addressKey), 'string', 'min:3'],
            "{$addressKey}.street" => ['sometimes', sprintf('required_with:%s', $addressKey), 'string', 'min:4'],
            "{$addressKey}.postalCode" => ['sometimes', sprintf('required_with:%s', $addressKey), 'string', 'min:3'],
        ];
    }
}
