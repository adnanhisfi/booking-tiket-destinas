<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpireBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Kedaluwarsakan booking pending yang melewati batas pembayaran dan kembalikan kuota';

    public function handle(BookingService $bookings): int
    {
        $count = $bookings->expireDueBookings();

        $this->info("{$count} booking dikedaluwarsakan.");

        return self::SUCCESS;
    }
}
