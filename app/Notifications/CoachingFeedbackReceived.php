<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\CoachingBooking;

class CoachingFeedbackReceived extends Notification
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
    $userLine = $this->booking->user ? ($this->booking->user->email . ' (' . $this->booking->user->name . ')') : 'Guest';
    $notes = $this->booking->notes ?? '-';
    return (new MailMessage)
            ->subject('New Coaching Feedback/Booking Note')
            ->line('A student submitted/updated notes for coaching:')
            ->line('User: ' . $userLine)
            ->line('Notes:')
            ->line($notes)
            ->action('Open Admin', url('/admin'));
    }
}
