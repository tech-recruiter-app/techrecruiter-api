<?php

declare(strict_types=1);

use App\Jobs\DeleteExpiredEmailVerificationTokens;
use App\Jobs\FetchValidTechnologies;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new FetchValidTechnologies)->name('fetchValidTechnologies')->weekly();

Schedule::job(new DeleteExpiredEmailVerificationTokens)->name('deleteExpiredEmailVerificationTokens')->daily();
