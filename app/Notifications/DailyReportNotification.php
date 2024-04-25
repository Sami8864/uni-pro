<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Traits\Notification as NotificationTrait; 
class DailyReportNotification extends Notification
{
    use Queueable;
    use NotificationTrait;
    /**
     * Create a new notification instance.
     */
    public $users;
    public function __construct($users)
    {
        $this->$users = $users;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['fcm'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toFcm(object $notifiable): array
    {
        // Define the content of the FCM notification here
        return [
            'title' => 'You typed 378' .  $this->users->types_points .' photos yesterday',
            'body' => 'You definitely went beast mode yesterday with the amount of photos you typed! ',
            // Additional data if needed
            'data' => [
                'users' => $this->users,
            ],
        ];
    }
}
