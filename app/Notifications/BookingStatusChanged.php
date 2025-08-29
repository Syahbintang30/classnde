<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\CoachingBooking;

class BookingStatusChanged extends Notification
{
    use Queueable;

    protected $booking;
    protected $status;

    public function __construct(CoachingBooking $booking, string $status)
    {
        $this->booking = $booking;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $b = $this->booking;
        $lines = [];
        if ($this->status === 'accepted') {
            $subject = 'Your coaching booking has been accepted';
            $greeting = 'Good news!';
            $lines[] = 'Your coaching booking (ID: ' . $b->id . ') has been accepted by the admin.';
            $lines[] = 'Scheduled time: ' . $b->booking_time;
            $action = route('coaching.session', $b->id);
            $actionText = 'Join session';
        } else {
            $subject = 'Your coaching booking has been rejected';
            $greeting = 'Hello';
            $lines[] = 'Your coaching booking (ID: ' . $b->id . ') has been rejected by the admin.';
            $lines[] = 'You can try booking a different slot or contact support.';
            $action = url('/coaching');
            $actionText = 'View coaching page';
        }

        $mail = (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting);

        foreach ($lines as $l) $mail->line($l);

        $mail->action($actionText, $action)
             ->line('Thank you for using our service.');

        return $mail;
    }
}
