<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\EmailVerificationTokenRepository;
use Illuminate\Foundation\Http\FormRequest;
use LogicException;

final class updateEmailVerificationStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('verify-email');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $validToken = function (string $attribute, mixed $value, callable $fail): void {
            if (! is_string($value)) {
                return;
            }

            $tokens = resolve(EmailVerificationTokenRepository::class);
            $user = $this->user();
            if (! $user instanceof \App\Models\User) {
                throw new LogicException('Authenticated user not found.');
            }

            if (! $tokens->exists($user, $value)) {
                $fail("The {$attribute} does not exist or is not valid.");
            }
        };

        return [
            'token' => ['required', 'string', $validToken],
        ];
    }
}
