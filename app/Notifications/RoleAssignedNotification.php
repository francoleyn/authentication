<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoleAssignedNotification extends Notification
{
    use Queueable;

    protected string $role;

    public function __construct(string $role)
    {
        $this->role = $role;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Role Assigned')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been assigned the role: ' . $this->role)
            ->line('You now have access to additional features.')
            ->action('View Dashboard', url('/'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Role Assigned',
            'message' => 'You have been assigned the role: ' . $this->role,
            'role' => $this->role,
            'type' => 'role_assigned',
        ];
    }
}
