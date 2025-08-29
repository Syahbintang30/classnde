<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\CoachingBooking;

class CoachInvited extends Notification
{
    use Queueable;

    protected $booking;

    public function __construct(CoachingBooking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $booking = $this->booking;
        $url = route('coaching.session', $booking->id);

        return (new MailMessage)
                    ->subject('You have been assigned as coach for a coaching session')
                    ->greeting('Hello ' . ($notifiable->name ?? 'Coach'))
                    ->line('You have been assigned as the coach for a coaching session.')
                    ->line('Booking ID: ' . $booking->id)
                    ->line('Time: ' . $booking->booking_time)
                    ->action('Join Session', $url)
                    ->line('If this was a mistake, please contact support.');
    }
}
