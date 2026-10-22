<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\TicketAvailability;
use App\Models\Voucher;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    public function test_pengunjung_dapat_membuat_booking_dan_kuota_ditahan(): void
    {
        $user = $this->customer();
        $date = now()->addDays(3)->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 10);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/bookings', [
                'destination_id' => $destination->id,
                'visit_date' => $date,
                'items' => [
                    ['ticket_type_id' => $ticketType->id, 'quantity' => 4],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_amount', 200000);

        $this->assertDatabaseHas('ticket_availability', [
            'ticket_type_id' => $ticketType->id,
            'sold' => 4,
        ]);
    }

    public function test_kuota_tidak_bisa_terjual_melebihi_batas(): void
    {
        $user = $this->customer();
        $date = now()->addDays(2)->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 5);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/bookings', [
                'destination_id' => $destination->id,
                'visit_date' => $date,
                'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 3]],
            ])->assertCreated();

        // Sisa kuota 2, permintaan 3 harus ditolak tanpa mengubah data.
        $this->actingAs($this->customer(), 'sanctum')
            ->postJson('/api/v1/bookings', [
                'destination_id' => $destination->id,
                'visit_date' => $date,
                'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 3]],
            ])->assertStatus(422);

        $this->assertSame(3, TicketAvailability::where('ticket_type_id', $ticketType->id)->value('sold'));
        $this->assertSame(1, Booking::count());
    }

    public function test_harga_pesanan_lama_tidak_berubah_saat_admin_mengubah_harga(): void
    {
        $user = $this->customer();
        $date = now()->addDay()->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 10, price: 50000);

        $booking = app(BookingService::class)->create($user, [
            'destination_id' => $destination->id,
            'visit_date' => $date,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 2]],
        ]);

        $ticketType->update(['price' => 90000]);

        $this->assertSame('50000.00', $booking->items()->first()->unit_price);
        $this->assertSame('100000.00', $booking->fresh()->total_amount);
    }

    public function test_booking_kedaluwarsa_mengembalikan_kuota(): void
    {
        $user = $this->customer();
        $date = now()->addDays(4)->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 10);

        $booking = app(BookingService::class)->create($user, [
            'destination_id' => $destination->id,
            'visit_date' => $date,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 2]],
        ]);

        $booking->update(['expires_at' => now()->subMinute()]);

        $this->artisan('bookings:expire')->assertSuccessful();

        $this->assertSame(Booking::STATUS_EXPIRED, $booking->fresh()->status);
        $this->assertSame(0, TicketAvailability::where('ticket_type_id', $ticketType->id)->value('sold'));
    }

    public function test_pengguna_tidak_dapat_melihat_pesanan_milik_orang_lain(): void
    {
        $owner = $this->customer();
        $other = $this->customer();
        $date = now()->addDay()->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date);

        $booking = app(BookingService::class)->create($owner, [
            'destination_id' => $destination->id,
            'visit_date' => $date,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 1]],
        ]);

        $this->actingAs($other, 'sanctum')
            ->getJson("/api/v1/bookings/{$booking->code}")
            ->assertForbidden();
    }

    public function test_voucher_diterapkan_pada_total(): void
    {
        $user = $this->customer();
        $date = now()->addDay()->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 10, price: 100000);

        Voucher::create([
            'code' => 'DISKON10',
            'type' => 'percent',
            'value' => 10,
            'min_amount' => 0,
            'max_discount' => null,
            'quota' => 10,
            'used' => 0,
            'start_at' => now()->subDay(),
            'end_at' => now()->addMonth(),
            'status' => 'active',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/bookings', [
                'destination_id' => $destination->id,
                'visit_date' => $date,
                'voucher_code' => 'diskon10',
                'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 1]],
            ])
            ->assertCreated()
            ->assertJsonPath('data.discount_amount', 10000)
            ->assertJsonPath('data.total_amount', 90000);
    }
}
