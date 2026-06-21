<?php

declare(strict_types=1);

namespace App\Rules;

use App\Values\JobTitle as DomainJobTitle;
use Closure;
use DomainException;
use Illuminate\Contracts\Validation\ValidationRule;

final class JobTitle implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            new DomainJobTitle($value);
        } catch (DomainException $e) {
            $fail($e->getMessage());
        }
    }
}
