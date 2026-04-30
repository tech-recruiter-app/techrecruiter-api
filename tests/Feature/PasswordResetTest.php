<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Override;
use Tests\TestCase;

final class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User<EmployerProfile|JobSeekerProfile>
     */
    private User $user;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::all()->random();
    }

    public function test_a_user_can_request_a_password_reset(): void
    {
        // ARRANGE
        Notification::fake();

        // ACT
        $response = $this->post(route('password.request'), ['email' => (string) $this->user->email]);

        // ASSERT
        $response->assertNoContent();
        Notification::assertSentTo(
            User::query()->where('email', $this->user->email)->firstOrFail(),
            ResetPassword::class
        );
    }

    public function test_a_user_can_reset_password_with_valid_token(): void
    {
        // ARRANGE
        $token = resolve(PasswordBroker::class)->createToken($this->user);

        // ACT
        $response = $this->post(route('password.reset'), [
            'email' => (string) $this->user->email,
            'token' => $token,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        // ASSERT
        $response->assertNoContent();
        $this->assertTrue(Hash::check('new-password', $this->user->refresh()->password));
    }

    public function test_a_user_cannot_reset_password_with_invalid_token(): void
    {
        // ACT
        $response = $this->post(route('password.reset'), [
            'email' => (string) $this->user->email,
            'token' => 'invalid-token',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        // ASSERT
        $response->assertUnprocessable();
    }
}
