<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\JobType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FetchJobPostingsRequest extends FormRequest
{
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
            'query' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'location' => ['sometimes', 'required', 'string', 'regex:/^[^,]+(,\s?[^,]+)?$/'],
            'recency' => ['sometimes', 'required', 'integer', Rule::in([1, 3, 7, 14])],
            'type' => ['sometimes', 'required', 'string', Rule::enum(JobType::class)],
        ];
    }
}
