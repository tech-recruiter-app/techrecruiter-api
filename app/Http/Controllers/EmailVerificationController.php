<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SendEmailVerificationNotification;
use App\Http\Requests\updateEmailVerificationStatusRequest;
use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use App\Models\User;
use App\Support\EmailVerificationTokenRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmailVerificationController extends Controller
{
    public function __construct(public EmailVerificationTokenRepository $tokens) {}

    public function requestEmailVerification(Request $request): JsonResponse
    {
        /** @var User<EmployerProfile|JobSeekerProfile> $user */
        $user = $request->user();

        new SendEmailVerificationNotification($this->tokens)->execute($user);

        return new JsonResponse(status: 202);
    }

    public function updateEmailVerificationStatus(updateEmailVerificationStatusRequest $request): JsonResponse
    {
        /** @var User<EmployerProfile|JobSeekerProfile> $user */
        $user = $request->user();

        $user->markEmailAsVerified();
        $this->tokens->delete($user);

        return new JsonResponse(status: 204);
    }
}
