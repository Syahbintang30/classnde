<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = $argv[1] ?? 2;
$booking = \App\Models\CoachingBooking::find($id);
if (! $booking) {
    echo "Booking id={$id} not found\n";
    exit(1);
}

echo "Booking id={$booking->id}\n";
echo "user_id={$booking->user_id}\n";
echo "booking_time={$booking->booking_time}\n";
echo "status={$booking->status}\n";
echo "twilio_room_sid=" . ($booking->twilio_room_sid ?? 'NULL') . "\n";

// show caching booking maybe
try {
    $cb = class_exists(\App\Models\CachingBooking::class) ? \App\Models\CachingBooking::where('booking_id', $booking->id)->first() : null;
    if ($cb) {
        echo "CachingBooking id={$cb->id} status={$cb->status} date={$cb->date} time={$cb->time}\n";
    }
} catch (\Throwable $e) {
    // ignore if caching table/model is removed
}

echo "\n";
