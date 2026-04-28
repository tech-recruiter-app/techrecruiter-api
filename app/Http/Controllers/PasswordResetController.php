<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

final class PasswordResetController extends Controller
{
    public function requestPasswordReset(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'string', 'email']]);

        $status = Password::sendResetLink(['email' => $request->email]);
        throw_unless(
            $status === Password::RESET_LINK_SENT,
            fn (string $status): ValidationException|TooManyRequestsHttpException|RuntimeException => match (true) {
                $status === Password::INVALID_USER => ValidationException::withMessages(['email' => 'The email address is not valid.']),
                $status === Password::INVALID_TOKEN => ValidationException::withMessages(['token' => 'The password reset token is not valid or does not exist.']),
                $status === Password::RESET_THROTTLED => new TooManyRequestsHttpException(message: 'Too many password reset attempts. Please try again later.'),
                default => new RuntimeException('An unexpected error occurred while attempting to send the password reset link.'),
            },
            $status
        );

        return new JsonResponse(status: 204);
    }

    public function resetPassword(PasswordResetRequest $request): JsonResponse
    {
        /** @var string $status */
        $status = Password::reset(
            $request->validated(),
            function (User $user, string $password): void {
                $user->forceFill(['password' => Hash::make($password)]);
                $user->save();
            }
        );

        throw_unless(
            $status === Password::PASSWORD_RESET,
            fn (string $status): ValidationException|TooManyRequestsHttpException|RuntimeException => match (true) {
                $status === Password::INVALID_TOKEN => ValidationException::withMessages(['token' => 'The password reset token is not valid or does not exist.']),
                $status === Password::RESET_THROTTLED => new TooManyRequestsHttpException(message: 'Too many password reset attempts. Please try again later.'),
                default => new RuntimeException('An unexpected error occurred while attempting to reset the password.'),
            },
            $status
        );

        return new JsonResponse(status: 204);
    }
}
