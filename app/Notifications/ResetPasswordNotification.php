<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $resetUrl) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Redefina sua senha da Metalar')
            ->view('emails.auth.reset-password', [
                'logoUrl' => url('/logo-email.svg'),
                'resetUrl' => $this->resetUrl,
                'userName' => $notifiable->name,
            ]);
    }
}
