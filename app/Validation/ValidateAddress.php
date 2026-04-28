<?php

declare(strict_types=1);

namespace App\Validation;

use App\Contracts\AddressVerifier;
use App\Exceptions\Domain\AddressVerificationException;
use App\Exceptions\Domain\InvalidAddress;
use App\Values\Address;
use Illuminate\Validation\Validator;

final readonly class ValidateAddress
{
    /**
     * @var string The base key under which the address fields are nested in the validated data
     */
    private string $addressKey;

    /**
     * @param  string|null  $parentKey  The parent key under which the address fields are nested in the validated data
     */
    public function __construct(?string $parentKey = null)
    {
        $this->addressKey = $parentKey ? "{$parentKey}.address" : 'address';
    }

    public function __invoke(Validator $validator): void
    {
        $addressData = data_get($validator->getData(), $this->addressKey);

        if (! is_array($addressData) || ! isset($addressData['country'], $addressData['municipality'])) {
            return;
        }

        $getComponentKey = (fn (string $component): string => sprintf('%s.%s', $this->addressKey, $component));

        try {
            $addressVerifier = resolve(AddressVerifier::class);
            $administrativeArea = data_get($addressData, 'administrativeArea');
            $street = data_get($addressData, 'street');
            $postalCode = data_get($addressData, 'postalCode');

            $addressVerifier->verify(
                new Address(
                    $addressData['country'],
                    $administrativeArea,
                    $addressData['municipality'],
                    $street,
                    $postalCode,
                )
            );
        } catch (AddressVerificationException|InvalidAddress $e) {
            $validator->errors()->addIf(
                $e->getCode() === AddressVerificationException::NONEXISTENT_COUNTRY || $e->getCode() === InvalidAddress::INVALID_COUNTRY,
                $getComponentKey('country'),
                $e->getMessage()
            );
            $validator->errors()->addIf(
                $e->getCode() === AddressVerificationException::NONEXISTENT_ADMINISTRATIVE_AREA || $e->getCode() === InvalidAddress::INVALID_ADMINISTRATIVE_AREA,
                $getComponentKey('administrativeArea'),
                $e->getMessage()
            );
            $validator->errors()->addIf(
                $e->getCode() === AddressVerificationException::NONEXISTENT_MUNICIPALITY || $e->getCode() === InvalidAddress::INVALID_MUNICIPALITY,
                $getComponentKey('municipality'),
                $e->getMessage()
            );
            $validator->errors()->addIf(
                $e->getCode() === AddressVerificationException::NONEXISTENT_STREET || $e->getCode() === InvalidAddress::INVALID_STREET,
                $getComponentKey('street'),
                $e->getMessage()
            );
            $validator->errors()->addIf(
                $e->getCode() === AddressVerificationException::NONEXISTENT_POSTAL_CODE || $e->getCode() === InvalidAddress::INVALID_POSTAL_CODE,
                $getComponentKey('postalCode'),
                $e->getMessage()
            );
        }
    }
}
