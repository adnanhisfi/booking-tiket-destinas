<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class TicketValidationTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    protected function paidBookingWithTickets(User $user): Booking
    {
        $today = now()->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($today, quota: 10);

        $booking = app(BookingService::class)->create($user, [
            'destination_id' => $destination->id,
            'visit_date' => $today,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 1]],
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'transaction_ref' => 'TRX-'.uniqid(),
            'method' => 'transfer',
            'amount' => $booking->total_amount,
            'status' => Payment::STATUS_PENDING,
            'proof_path' => 'payment-proofs/bukti.jpg',
        ]);

        app(PaymentService::class)->verifyManual($payment, $this->admin());

        return $booking->fresh();
    }

    public function test_tiket_valid_dapat_digunakan_sekali(): void
    {
        $user = $this->customer();
        $booking = $this->paidBookingWithTickets($user);
        $ticket = $booking->tickets()->first();

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/v1/admin/tickets/validate', ['code' => $ticket->code])
            ->assertOk()
            ->assertJsonPath('result', 'valid');

        $this->assertSame(Ticket::STATUS_USED, $ticket->fresh()->status);
    }

    public function test_qr_yang_sama_ditolak_saat_dipindai_dua_kali(): void
    {
        $user = $this->customer();
        $booking = $this->paidBookingWithTickets($user);
        $ticket = $booking->tickets()->first();
        $admin = $this->admin();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/tickets/validate', ['code' => 'JLK:'.$ticket->code])
            ->assertOk()
            ->assertJsonPath('result', 'valid');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/tickets/validate', ['code' => 'JLK:'.$ticket->code])
            ->assertOk()
            ->assertJsonPath('result', 'already_used')
            ->assertJsonPath('valid', false);
    }

    public function test_tiket_ditolak_saat_dipakai_pada_tanggal_berbeda(): void
    {
        $user = $this->customer();
        $tomorrow = now()->addDay()->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($tomorrow, quota: 5);

        $booking = app(BookingService::class)->create($user, [
            'destination_id' => $destination->id,
            'visit_date' => $tomorrow,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 1]],
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'transaction_ref' => 'TRX-'.uniqid(),
            'method' => 'transfer',
            'amount' => $booking->total_amount,
            'status' => Payment::STATUS_PENDING,
            'proof_path' => 'payment-proofs/bukti.jpg',
        ]);
        app(PaymentService::class)->verifyManual($payment, $this->admin());

        $ticket = $booking->fresh()->tickets()->first();

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/v1/admin/tickets/validate', ['code' => $ticket->code])
            ->assertOk()
            ->assertJsonPath('result', 'wrong_date');
    }

    public function test_kode_qr_tidak_dikenal_menghasilkan_tidak_valid(): void
    {
        $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/v1/admin/tickets/validate', ['code' => 'KODE-PALSU'])
            ->assertNotFound()
            ->assertJsonPath('result', 'invalid');
    }

    public function test_driver_manual_verifikasi_admin_menerbitkan_tiket(): void
    {
        $user = $this->customer();
        $booking = $this->paidBookingWithTickets($user);

        $this->assertSame(Booking::STATUS_PAID, $booking->status);
        $this->assertSame(1, $booking->tickets()->count());
    }

    public function test_qr_svg_dapat_dihasilkan_dan_hanya_pemilik_bisa_akses(): void
    {
        $user = $this->customer();
        $booking = $this->paidBookingWithTickets($user);
        $ticket = $booking->tickets()->first();

        $svg = app(TicketService::class)->qrSvg($ticket);
        $this->assertStringContainsString('<svg', $svg);

        $this->actingAs($user, 'sanctum')
            ->get("/api/v1/tickets/{$ticket->code}/qr")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');

        $this->actingAs($this->customer(), 'sanctum')
            ->getJson("/api/v1/tickets/{$ticket->code}")
            ->assertForbidden();
    }
}
