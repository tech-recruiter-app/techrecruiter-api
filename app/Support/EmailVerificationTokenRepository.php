<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LogicException;

final readonly class EmailVerificationTokenRepository
{
    public function __construct(
        private ConnectionInterface $database,
        private Hasher $hasher,
        private string $hashKey
    ) {}

    /**
     * Create a new email verification token.
     *
     * @param  MustVerifyEmail  $user  The user to create a token for.
     * @return string The generated email verification token.
     */
    public function create(MustVerifyEmail $user): string
    {
        // Validate that the user's email is not already verified before creating a token.
        if ($user->hasVerifiedEmail()) {
            throw new LogicException('Email address is already verified.');
        }

        $token = hash_hmac('sha256', Str::random(), $this->hashKey);

        $this->database->transaction(function () use ($user, $token): void {
            $this->deleteExisting($user);

            $this->getTable()->insert([
                'email' => $user->getEmailForVerification(),
                'token' => $this->hasher->make($token),
                'expires_at' => new Carbon()->addHours(24),
            ]);
        });

        return $token;
    }

    /**
     * Determine if a token exists for the given user.
     *
     * @param  MustVerifyEmail  $user  The user to check for the token.
     * @param  string  $token  The token to check for existence.
     * @return bool True if the token exists for the email address, false otherwise.
     */
    public function exists(MustVerifyEmail $user, string $token): bool
    {
        $record = $this->getTable()->where(
            'email',
            $user->getEmailForVerification()
        )->first();

        if (isset($record)) {
            $record = (array) $record;
            assert(is_string($record['token']) && is_string($record['expires_at']));

            return ! new Carbon($record['expires_at'])->isPast() && $this->hasher->check($token, $record['token']);
        }

        return false;
    }

    /**
     * Delete the token record for the given user.
     *
     * @param  MustVerifyEmail  $user  The user to delete the token for.
     */
    public function delete(MustVerifyEmail $user): void
    {
        $this->deleteExisting($user);
    }

    /**
     * Delete all expired tokens from the repository.
     */
    public function deleteExpired(): void
    {
        $this->getTable()->where('expires_at', '<', new Carbon())->delete();
    }

    /**
     * Delete all existing tokens from the database.
     *
     * @param  MustVerifyEmail  $user  The user to delete existing tokens for.
     */
    private function deleteExisting(MustVerifyEmail $user): void
    {
        $this->getTable()->where('email', $user->getEmailForVerification())->delete();
    }

    private function getTable(): Builder
    {
        return $this->database->table('email_verification_tokens');
    }
}
