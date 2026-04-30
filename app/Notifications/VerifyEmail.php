<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use LogicException;
use Uri\WhatWg\Url;

final class VerifyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $token  The email verification token to be included in the notification.
     */
    public function __construct(public string $token) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        /** @var string */
        $app_url = config('app.url');
        if (! is_string($emailVerificationUrl = config('app.frontend.email_verification_url')) || $emailVerificationUrl === '') {
            throw new LogicException('Frontend email verification URL must be configured.');
        }

        return new MailMessage()
            ->from(sprintf('no-reply@%s', new Url($app_url)->getAsciiHost()))
            ->subject('Verify Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', sprintf('%s?token=%s', $emailVerificationUrl, $this->token))
            ->line('If you did not create an account, no further action is required.');
    }
}
