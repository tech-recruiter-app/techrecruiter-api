<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Traits\RetrievesAuthenticationGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AuthenticationTest extends TestCase
{
    use RefreshDatabase, RetrievesAuthenticationGuard;

    public function test_a_user_can_login(): void
    {
        // ARRANGE
        $credentials = $this->credentials();
        $user = User::query()->where('email', $credentials['email'])->firstOrFail();

        // ACT
        $response = $this->post(route('authentication-tokens.create'), $credentials);

        // ASSERT
        $response->assertSuccessful()->assertJsonStructure(['token', 'expiresAt']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_user_cannot_login_with_invalid_credentials(): void
    {
        // ARRANGE
        $credentials = $this->credentials(false);

        // ACT
        $response = $this->post(route('authentication-tokens.create'), $credentials);

        // ASSERT
        $response->assertUnauthorized();
    }

    public function test_an_authenticated_user_cannot_login(): void
    {
        // ARRANGE
        $user = User::all()->random();

        // ACT
        $response = $this->actingAs($user)->post(route('authentication-tokens.create'), $this->credentials());

        // ASSERT
        $response->assertForbidden();
    }

    public function test_a_user_can_logout(): void
    {
        // ARRANGE
        $user = User::all()->random();

        // ACT
        $response = $this->actingAs($user)->delete(route('authentication-tokens.delete'));

        // ASSERT
        $response->assertNoContent();
        $this->assertGuest();
    }

    public function test_an_unauthenticated_user_cannot_logout(): void
    {
        // ACT
        $response = $this->deleteJson(route('authentication-tokens.delete'));

        // ASSERT
        $response->assertUnauthorized();
    }

    public function test_frontend_can_fetch_the_authenticated_user(): void
    {
        // ARRANGE
        $user = User::all()->random();

        // ACT
        $response = $this->actingAs($user)->get(route('authenticated-user.show'));

        // ASSERT
        $response->assertSuccessful();
    }

    public function test_user_can_refresh_their_token(): void
    {
        // ARRANGE
        $tokenFactory = resolve(\PHPOpenSourceSaver\JWTAuth\JWT::class);
        $token = $tokenFactory->fromSubject(User::all()->random());
        $this->getGuard()->setToken($token);

        // ACT
        $response = $this->withHeader('Authorization', "Bearer $token")->put(route('authentication-tokens.refresh'));

        // ASSERT
        $response->assertSuccessful()->assertJsonStructure(['token', 'expiresAt']);
        $this->assertNotEquals($token, $response->json('token'));
    }

    /**
     * @return array{email:string, password:string}
     */
    protected function credentials(bool $valid = true): array
    {
        $email = DB::table('users')->inRandomOrder()->value('email');
        assert(is_string($email));

        return [
            'email' => $email,
            'password' => $valid ? 'password' : 'wrong-password',
        ];
    }
}
