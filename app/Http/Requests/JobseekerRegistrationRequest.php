<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\Link;
use App\Traits\HasAccountValidationRules;
use App\Validation\ValidateAddress;
use Illuminate\Foundation\Http\FormRequest;
use Propaganistas\LaravelPhone\Rules\Phone;

final class JobseekerRegistrationRequest extends FormRequest
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
        return [
            ...$this->accountRules(),
            'firstname' => ['required', 'string', 'min:3', 'max:255'],
            'lastname' => ['required', 'string', 'min:3', 'max:255'],
            'phoneNumber' => ['sometimes', 'required', new Phone()->type('mobile')->international(), 'unique:job_seeker_profiles,phone_number'],
            'resumeLink' => ['required', 'string', new Link, 'unique:job_seeker_profiles,resume_link'],
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
