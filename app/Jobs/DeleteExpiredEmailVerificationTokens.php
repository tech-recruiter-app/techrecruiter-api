<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Support\EmailVerificationTokenRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class DeleteExpiredEmailVerificationTokens implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tokens = resolve(EmailVerificationTokenRepository::class);
        $tokens->deleteExpired();
    }
}
