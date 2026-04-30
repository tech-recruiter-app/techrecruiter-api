<?php

declare(strict_types=1);

namespace App\Rules;

use App\Contracts\LinkVerifier;
use App\Exceptions\Domain\LinkVerificationException;
use App\Values\Link as DomainLink;
use Closure;
use DomainException;
use Illuminate\Contracts\Validation\ValidationRule;

final class Link implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, $value, Closure $fail): void
    {
        try {
            $linkVerifier = resolve(LinkVerifier::class);
            $linkVerifier->verify(new DomainLink($value));
        } catch (DomainException $e) {
            $message = (string) preg_replace('/[.+?]/', ' ', $e->getMessage());
            $fail($message);
        } catch (LinkVerificationException) {
            $fail("The {$attribute} must be a real link to a resource.");
        }
    }
}
