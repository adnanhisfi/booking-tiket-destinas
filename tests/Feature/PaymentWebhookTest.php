<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'payment.driver' => 'sandbox',
            'payment.webhook_secret' => 'rahasia-uji',
        ]);
    }

    protected function makeBooking(User $user): Booking
    {
        $date = now()->addDays(2)->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 10);

        return app(BookingService::class)->create($user, [
            'destination_id' => $destination->id,
            'visit_date' => $date,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 2]],
        ]);
    }

    public function test_webhook_melunasi_booking_dan_menerbitkan_tiket(): void
    {
        $user = $this->customer();
        $booking = $this->makeBooking($user);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->code}/payments", ['method' => 'qris'])
            ->assertCreated();

        $ref = Payment::first()->transaction_ref;

        $response = $this->postWebhook([
            'transaction_ref' => $ref,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'order_id' => 'ORDER-1',
        ])->assertOk();

        $this->assertSame('paid', $booking->fresh()->status);
        $this->assertSame(Payment::STATUS_PAID, Payment::first()->status);
        $this->assertSame(2, Ticket::count());
        $this->assertNotNull($response);
    }

    public function test_webhook_ganda_tidak_menerbitkan_tiket_tambahan(): void
    {
        $user = $this->customer();
        $booking = $this->makeBooking($user);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->code}/payments", ['method' => 'va']);

        $ref = Payment::first()->transaction_ref;
        $payload = [
            'transaction_ref' => $ref,
            'transaction_status' => 'settlement',
            'payment_type' => 'va',
        ];

        $this->postWebhook($payload)->assertOk();
        $this->postWebhook($payload)->assertOk();

        $this->assertSame(2, Ticket::count());
    }

    public function test_signature_webhook_tidak_valid_ditolak(): void
    {
        $user = $this->customer();
        $booking = $this->makeBooking($user);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->code}/payments", ['method' => 'qris']);

        $ref = Payment::first()->transaction_ref;
        $body = json_encode(['transaction_ref' => $ref, 'transaction_status' => 'settlement']);

        $this->call('POST', '/api/v1/payments/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE' => 'signature-palsu',
        ], $body)->assertUnauthorized();

        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_status_booking_kedaluwarsa_saat_pembayaran_lewat_batas(): void
    {
        $user = $this->customer();
        $booking = $this->makeBooking($user);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->code}/payments", ['method' => 'qris'])
            ->assertCreated();

        $booking->update(['expires_at' => now()->subMinute()]);

        $this->artisan('bookings:expire')->assertSuccessful();

        $this->assertSame('expired', $booking->fresh()->status);
        $this->assertSame(Payment::STATUS_EXPIRED, Payment::first()->status);
    }

    protected function postWebhook(array $payload)
    {
        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, 'rahasia-uji');

        return $this->call('POST', '/api/v1/payments/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE' => $signature,
        ], $body);
    }
}
