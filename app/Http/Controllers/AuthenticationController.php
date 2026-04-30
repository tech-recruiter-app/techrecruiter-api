<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\LoginFailedException;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Traits\RetrievesAuthenticationGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;
use LogicException;

final class AuthenticationController extends Controller
{
    use RetrievesAuthenticationGuard;

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->getGuard()->attempt($request->validated());
        throw_unless(is_string($token), LoginFailedException::class);

        return $this->respondWithToken($token);
    }

    public function showAuthenticatedUser(): JsonResponse
    {
        /** @var \App\Models\User<\App\Models\EmployerProfile|\App\Models\JobSeekerProfile> */
        $user = $this->getGuard()->user();
        $user->load('profile');

        return new UserResource($user)->response();
    }

    public function logout(): JsonResponse
    {
        $this->getGuard()->logout();

        return new JsonResponse(status: 204);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(
            $this->getGuard()->refresh()
        );
    }

    private function respondWithToken(string $token): JsonResponse
    {
        if (is_null($tokenTtl = $this->getGuard()->getTTL())) {
            throw new LogicException('The token time-to-live must be set.');
        }

        return new JsonResponse([
            'token' => $token,
            'expiresAt' => Date::now()->addMinutes($tokenTtl)->toAtomString(),
        ]);
    }
}
