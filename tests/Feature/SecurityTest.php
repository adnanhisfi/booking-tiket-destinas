<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    public function test_seluruh_respons_web_membawa_header_keamanan_dasar(): void
    {
        $this->get('/masuk')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=()');
    }

    public function test_respons_api_juga_membawa_header_keamanan(): void
    {
        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_respons_error_web_dan_api_tetap_membawa_header_keamanan(): void
    {
        $this->get('/halaman-tidak-ada')
            ->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');

        $this->getJson('/api/v1/bookings')
            ->assertUnauthorized()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
    }

    public function test_login_web_dibatasi_setelah_lima_percobaan(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->from('/masuk')->post('/masuk', [
                'email' => 'penyerang@example.test',
                'password' => 'password-salah',
            ])->assertRedirect('/masuk');
        }

        $this->from('/masuk')->post('/masuk', [
            'email' => 'penyerang@example.test',
            'password' => 'password-salah',
        ])->assertStatus(429);
    }

    public function test_webhook_menolak_secret_default_yang_tidak_aman(): void
    {
        config([
            'payment.driver' => 'sandbox',
            'payment.webhook_secret' => 'change-me-production-secret',
        ]);

        $body = json_encode(['transaction_ref' => 'TRF-TIDAK-AMAN']);
        $signature = hash_hmac('sha256', $body, 'change-me-production-secret');

        $this->call('POST', '/api/v1/payments/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE' => $signature,
        ], $body)->assertUnauthorized();
    }

    public function test_upload_bukti_menolak_berkas_dengan_ekstensi_berbahaya(): void
    {
        Storage::fake('public');

        $user = $this->customer();
        $payment = $this->pendingPayment($user);

        $this->actingAs($user)
            ->from(route('payment.show', $payment->transaction_ref))
            ->post(route('payment.proof', $payment->transaction_ref), [
                'proof' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
            ])
            ->assertRedirect(route('payment.show', $payment->transaction_ref))
            ->assertSessionHasErrors('proof');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'proof_path' => null,
        ]);
    }

    public function test_bukti_lama_dihapus_saat_pengunjung_mengunggah_bukti_baru(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('payment-proofs/bukti-lama.jpg', 'file lama');

        $user = $this->customer();
        $payment = $this->pendingPayment($user);
        $payment->update(['proof_path' => 'payment-proofs/bukti-lama.jpg']);

        $this->actingAs($user)
            ->from(route('payment.show', $payment->transaction_ref))
            ->post(route('payment.proof', $payment->transaction_ref), [
                'proof' => UploadedFile::fake()->create('bukti-baru.jpg', 100, 'image/jpeg'),
            ])
            ->assertRedirect(route('booking.status', $payment->booking->code));

        Storage::disk('public')->assertMissing('payment-proofs/bukti-lama.jpg');
        Storage::disk('public')->assertExists($payment->fresh()->proof_path);
    }

    protected function pendingPayment(User $user): Payment
    {
        $date = now()->addDay()->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date);

        $booking = app(BookingService::class)->create($user, [
            'destination_id' => $destination->id,
            'visit_date' => $date,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 1]],
        ]);

        return Payment::create([
            'booking_id' => $booking->id,
            'transaction_ref' => 'TRF-SECURITY-'.uniqid(),
            'method' => 'transfer',
            'amount' => $booking->total_amount,
            'status' => Payment::STATUS_PENDING,
        ]);
    }
}
