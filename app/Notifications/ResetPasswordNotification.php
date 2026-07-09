<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * Build the mail representation of the notification.
     * Uses the BiteHub branded password_reset email template.
     */
    public function toMail($notifiable)
    {
        $resetUrl = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('🔐 Reset Your BiteHub Password')
            ->view('emails.password_reset', [
                'resetUrl'  => $resetUrl,
                'userName'  => $notifiable->FullName ?? $notifiable->name ?? 'User',
                'userEmail' => $notifiable->Email ?? $notifiable->email ?? '',
            ]);
    }
}
