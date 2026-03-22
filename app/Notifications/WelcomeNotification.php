<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Our Application!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for registering with us.')
            ->line('Your account has been created successfully.')
            ->action('Login Now', url('/'))
            ->line('We are excited to have you on board!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Welcome!',
            'message' => 'Thank you for registering. Your account has been created successfully.',
            'type' => 'welcome',
        ];
    }
}
