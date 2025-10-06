<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class WelcomeWithPasswordNotification extends Notification
{
    use Queueable;

    protected string $password;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password)
    {
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ClassNDE - Your Account Details')
            ->greeting('Welcome to ClassNDE!')
            ->line('Your account has been created successfully after your payment was confirmed.')
            ->line('Here are your login credentials:')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Password:** ' . $this->password)
            ->line('Please keep this information secure and consider changing your password after your first login.')
            ->action('Login to Your Account', url('/login'))
            ->line('If you did not create this account, please contact our support team.')
            ->line('Thank you for joining ClassNDE!');
    }
}