<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmail
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('メール認証')
                    ->line('あなたのアカウントを認証するために、以下のリンクをクリックしてください。')
                    // 正しいURLを生成するために、$notifiable->getVerificationUrl()を使用
                    ->action('認証する', $this->verificationUrl($notifiable))
                    ->line('もしこのメールに心当たりがなければ、無視してこのメールを削除してください。');
    }
}
