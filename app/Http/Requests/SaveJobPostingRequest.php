<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\AddressData;
use App\Data\JobCompensationData;
use App\Data\NewJobPostingData;
use App\Enums\JobCompensationCurrency;
use App\Enums\JobCompensationType;
use App\Enums\JobPostingStatus;
use App\Enums\JobType;
use App\Models\JobPosting;
use App\Rules\JobTitle;
use App\Rules\Link;
use App\Traits\HasAddressValidationRules;
use App\Validation\ValidateAddress;
use App\Validation\ValidateEducationalQualification;
use App\Validation\ValidateJobCompensation;
use App\Validation\ValidateTechStack;
use App\Validation\VerifyUserIsEmployer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class SaveJobPostingRequest extends FormRequest
{
    use HasAddressValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->isMethod('POST') ?
            Gate::allows('create', JobPosting::class) :
            Gate::allows('update', $this->route('jobPosting'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_title' => [Rule::when($this->isMethod('PATCH'), 'sometimes'), 'required', 'string', 'min:3', 'max:255', new JobTitle, Rule::unique('job_postings', 'job_title')->where('employer_id', $this->user()?->id)],
            'job_type' => [Rule::when($this->isMethod('PATCH'), 'sometimes'), 'required', 'string', Rule::enum(JobType::class)],
            ...$this->addressRules('job_location', optional: true),
            'job_compensation' => [Rule::when($this->isMethod('PATCH'), 'sometimes'), 'array:type,minimum,maximum,currency', 'required_array_keys:type,minimum,maximum,currency'],
            'job_compensation.type' => ['required_with:job_compensation', 'string', Rule::enum(JobCompensationType::class)],
            'job_compensation.minimum' => ['required_with:job_compensation', 'integer:strict', 'min_digits:2'],
            'job_compensation.maximum' => ['required_with:job_compensation', 'integer:strict', 'min_digits:2', 'gte:job_compensation.minimum'],
            'job_compensation.currency' => ['required_with:job_compensation', 'string', Rule::enum(JobCompensationCurrency::class)],
            'job_stack' => [Rule::when($this->isMethod('PATCH'), 'sometimes'), 'required', 'list', 'min:1'],
            'job_stack.*' => ['required_with:job_stack', 'string', 'min:2', 'max:50'],
            'job_responsibilities' => [Rule::when($this->isMethod('PATCH'), 'sometimes'), 'required', 'nullable', 'list', 'min:1'],
            'job_responsibilities.*' => ['required_with:job_responsibilities', 'string', 'min:10'],
            'job_requirements' => [Rule::when($this->isMethod('PATCH'), 'sometimes'), 'required', 'nullable', 'list', 'min:1'],
            'job_requirements.*' => ['required_with:job_requirements', 'string', 'min:10'],
            'job_benefits' => [Rule::when($this->isMethod('PATCH'), 'sometimes'), 'required', 'nullable', 'list', 'min:1'],
            'job_benefits.*' => ['required_with:job_benefits', 'string', 'min:10'],
            'job_educational_requirement' => [Rule::when($this->isMethod('PATCH'), 'sometimes'), 'required', 'nullable', 'array:degree,field', 'required_array_keys:degree,field'],
            'job_educational_requirement.degree' => ['required_with:job_educational_requirement', 'string'],
            'job_educational_requirement.field' => ['required_with:job_educational_requirement', 'string'],
            'job_starts_on' => [Rule::when($this->isMethod('PATCH'), 'sometimes'), 'required', 'nullable', 'date', 'after_or_equal:today'],
            'link' => [Rule::when($this->isMethod('PATCH'), 'sometimes'), 'required', 'nullable', 'string', new Link, 'unique:job_postings,link'],
            'status' => [Rule::when($this->isMethod('PATCH'), 'sometimes'), 'required', 'string', Rule::enum(JobPostingStatus::class)],
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
            new VerifyUserIsEmployer,
            new ValidateAddress('job_location'),
            new ValidateJobCompensation,
            new ValidateTechStack,
            new ValidateEducationalQualification,
        ];
    }

    /**
     * Create a DTO from the validated request data.
     */
    public function toDto(): NewJobPostingData
    {
        return new NewJobPostingData(
            $this->validated('job_title'),
            $this->validated('job_type'),
            new AddressData(
                $this->validated('job_location.country'),
                $this->validated('job_location.municipality'),
                $this->validated('job_location.street'),
                $this->validated('job_location.administrative_area'),
                $this->validated('job_location.postal_code'),
            ),
            new JobCompensationData(
                $this->validated('job_compensation.minimum'),
                $this->validated('job_compensation.maximum'),
                $this->validated('job_compensation.currency'),
                $this->validated('job_compensation.type'),
            ),
            $this->validated('job_stack'),
            $this->validated('job_responsibilities'),
            $this->validated('job_requirements'),
            $this->validated('job_benefits'),
            $this->input('employer_id'),
            $this->validated('status'),
            $this->filled('job_educational_requirement') ? [
                'degree' => $this->validated('job_educational_requirement.degree'),
                'field' => $this->validated('job_educational_requirement.field'),
            ] : null,
            $this->input('job_starts_on'),
            $this->input('link'),
        );
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        if ($this->isMethod('POST')) {
            $this->merge(['employer_id' => $this->user()?->id]);
        }
    }
}
