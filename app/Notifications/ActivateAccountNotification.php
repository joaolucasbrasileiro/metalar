<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivateAccountNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $activationUrl) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ative sua conta na Metalar')
            ->view('emails.auth.activate-account', [
                'activationUrl' => $this->activationUrl,
                'logoUrl' => url('/logo-email.svg'),
                'userName' => $notifiable->name,
            ]);
    }
}
