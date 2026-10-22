<?php

namespace App\Jobs;

use App\Mail\EticketMail;
use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEticketJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $bookingId) {}

    public function handle(): void
    {
        $booking = Booking::with(['tickets', 'destination', 'user'])->find($this->bookingId);

        if (! $booking || ! $booking->user?->email || $booking->tickets->isEmpty()) {
            return;
        }

        Mail::to($booking->user->email)->send(new EticketMail($booking));
    }
}
