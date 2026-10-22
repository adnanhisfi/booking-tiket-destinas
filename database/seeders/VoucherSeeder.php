<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $kuta = Destination::where('slug', 'pantai-kuta-bali')->first();

        Voucher::updateOrCreate(
            ['code' => 'JELAJAH10'],
            [
                'type' => Voucher::TYPE_PERCENT,
                'value' => 10,
                'min_amount' => 50000,
                'max_discount' => 50000,
                'quota' => 100,
                'used' => 0,
                'destination_id' => null,
                'start_at' => now()->subDay(),
                'end_at' => now()->addMonths(3),
                'status' => 'active',
            ]
        );

        Voucher::updateOrCreate(
            ['code' => 'KUTA20'],
            [
                'type' => Voucher::TYPE_FIXED,
                'value' => 20000,
                'min_amount' => 100000,
                'max_discount' => null,
                'quota' => 50,
                'used' => 0,
                'destination_id' => $kuta?->id,
                'start_at' => now()->subDay(),
                'end_at' => now()->addMonths(2),
                'status' => 'active',
            ]
        );
    }
}
