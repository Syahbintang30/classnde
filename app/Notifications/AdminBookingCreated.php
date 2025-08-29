<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\CoachingBooking;

class AdminBookingCreated extends Notification
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
        $b = $this->booking;
        $url = url('/admin/coaching/bookings');

        return (new MailMessage)
                    ->subject('New coaching booking #' . $b->id)
                    ->greeting('Admin')
                    ->line('A new coaching booking has been created.')
                    ->line('Booking ID: ' . $b->id)
                    ->line('User: ' . optional($b->user)->email)
                    ->line('Time: ' . $b->booking_time)
                    ->action('View bookings in admin', $url)
                    ->line('You can manage capacities and assignments from the admin panel.');
    }
}
