<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Contracts\DomainNameVerifier;
use App\Exceptions\Domain\DomainNameVerificationException;
use App\Traits\HasAccountValidationRules;
use App\Validation\ValidateAddress;
use App\Values\CompanyDomain;
use Closure;
use DomainException;
use Illuminate\Foundation\Http\FormRequest;

final class EmployerRegistrationRequest extends FormRequest
{
    use HasAccountValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Custom validation rule for validating domain names
        $domain = function (string $attribute, $value, Closure $fail): void {
            try {
                $domainVerifier = resolve(DomainNameVerifier::class);
                $domainVerifier->verify(new CompanyDomain($value));
            } catch (DomainException) {
                $fail("The {$attribute} must be a valid domain name.");
            } catch (DomainNameVerificationException) {
                $fail("The {$attribute} must be a real domain name.");
            }
        };

        return [
            ...$this->accountRules(),
            'companyName' => ['required', 'string', 'min:3', 'max:255', 'unique:employer_profiles,company_name'],
            'companyDomain' => ['required', 'string', $domain, 'unique:employer_profiles,company_domain'],
            'companyDescription' => ['required', 'string', 'min:200'],
        ];
    }

    /**
     * Get the validation rules that should be applied after the default validation rules.
     *
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            new ValidateAddress,
        ];
    }
}
