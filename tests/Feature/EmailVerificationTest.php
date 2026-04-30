<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Support\EmailVerificationTokenRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unverified_user_can_verify_their_email_address(): void
    {
        // ARRANGE
        $user = User::query()->where('email_verified_at')->firstOrFail();
        $token = resolve(EmailVerificationTokenRepository::class)->create($user);

        // ACT
        $response = $this->actingAs($user)->patch(route('email-verification.update'), ['token' => $token]);

        // ASSERT
        $response->assertNoContent();
        $this->assertInstanceOf(\Carbon\CarbonImmutable::class, $user->refresh()->email_verified_at);
    }

    public function test_a_verified_user_cannot_reverify_their_email_address(): void
    {
        // ARRANGE
        $user = User::query()->whereNot('email_verified_at')->firstOrFail();
        $token = Str::random();

        // ACT
        $response = $this->actingAs($user)->patch(route('email-verification.update'), ['token' => $token]);

        // ASSERT
        $response->assertForbidden();
    }
}
