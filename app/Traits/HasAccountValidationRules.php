<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Validation\Rules\Password;

trait HasAccountValidationRules
{
    use HasAddressValidationRules;

    /**
     * Get validation rules that apply to user accounts.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function accountRules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->uncompromised()],
            ...$this->addressRules(),
        ];
    }
}
