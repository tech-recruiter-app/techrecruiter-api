<?php

declare(strict_types=1);

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['message' => 'API Works']));

Route::controller(AuthenticationController::class)->group(function (): void {
    Route::prefix('authentication')->group(function (): void {
        Route::post('/tokens', 'login')->middleware(['guest', 'throttle:6,1'])->name('authentication-tokens.create');
        Route::delete('/tokens/current', 'logout')->middleware('auth')->name('authentication-tokens.delete');
        Route::put('/tokens/current', 'refresh')->middleware('auth')->name('authentication-tokens.refresh');
    });
    Route::get('/users/me', 'showAuthenticatedUser')->middleware('auth')->name('authenticated-user.show');
});

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

Route::controller(PasswordResetController::class)->middleware('guest')->group(function (): void {
    Route::post('/password-reset-requests', 'requestPasswordReset')->middleware('throttle:6,1')->name('password.request');
    Route::post('/passwords', 'resetPassword')->name('password.reset');
});
