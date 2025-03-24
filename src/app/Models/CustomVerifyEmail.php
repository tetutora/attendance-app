<?php

namespace App\Models;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    // メールの内容を定義
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Please Verify Your Email Address')
            ->line('Click the button below to verify your email address.')
            ->action('Verify Email', url('/verify-email'));
    }
}
