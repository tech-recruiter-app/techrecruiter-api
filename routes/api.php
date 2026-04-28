<?php

declare(strict_types=1);

use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['message' => 'API Works']));

Route::controller(UserController::class)->group(function (): void {
    Route::prefix('users')->group(function (): void {
        Route::middleware('guest')->group(function (): void {
            Route::post('/employers', 'registerEmployer')->name('employers.create');
            Route::post('/jobseekers', 'registerJobseeker')->name('jobseekers.create');
        });
    });
});

Route::controller(EmailVerificationController::class)->middleware('auth')->can('verify-email')->group(function (): void {
    Route::post('/users/me/email-verification-requests', 'requestEmailVerification')->middleware('throttle:6,1')->name('email-verification.request');
    Route::patch('/users/me/email-verification-status', 'updateEmailVerificationStatus')->name('email-verification.update');
});
