<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use App\Models\User;
use App\Notifications\VerifyEmail;
use App\Support\EmailVerificationTokenRepository;

final readonly class SendEmailVerificationNotification
{
    public function __construct(
        private EmailVerificationTokenRepository $tokens,
    ) {}

    /**
     * Execute the action.
     *
     * @param  User<EmployerProfile|JobSeekerProfile>  $user  The user to send the email verification link to.
     */
    public function execute(User $user): void
    {
        $user->notify(new VerifyEmail(
            $this->tokens->create($user)
        ));
    }
}
