<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class WebBookingFlowTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    /**
     * @return array{0: Destination, 1: TicketType, 2: Booking}
     */
    protected function createBooking(User $user, int $quantity = 2, ?string $date = null): array
    {
        $date ??= now()->addDays(3)->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 10, price: 50000);

        $this->actingAs($user)
            ->post(route('booking.store', $destination->slug), [
                'visit_date' => $date,
                'items' => [
                    ['ticket_type_id' => $ticketType->id, 'quantity' => $quantity],
                ],
                'customer_name' => $user->name,
                'customer_phone' => $user->phone,
            ])
            ->assertRedirect();

        $booking = Booking::query()->latest('id')->firstOrFail();

        return [$destination, $ticketType, $booking];
    }

    public function test_form_booking_menampilkan_kuota_dan_harga_dinamis(): void
    {
        $user = $this->customer();
        $date = now()->addDays(2)->toDateString();
        [$destination] = $this->destinationWithTicket($date, quota: 7, price: 75000);

        $this->actingAs($user)
            ->get(route('booking.create', $destination->slug).'?tanggal='.$date)
            ->assertOk()
            ->assertSee($destination->name)
            ->assertSee('Sisa 7 tiket')
            ->assertSee('75.000');
    }

    public function test_tanggal_tidak_valid_pada_form_booking_tidak_menimbulkan_error(): void
    {
        $user = $this->customer();
        [$destination] = $this->destinationWithTicket(now()->addDays(2)->toDateString());

        foreach (['abc', '2020-01-01', '9999-12-31'] as $invalidDate) {
            $this->actingAs($user)
                ->get(route('booking.create', $destination->slug).'?tanggal='.$invalidDate)
                ->assertOk();
        }

        $this->actingAs($user)
            ->get(route('booking.create', $destination->slug).'?tanggal[]=abc')
            ->assertOk();
    }

    public function test_pengunjung_dapat_membuat_booking_lewat_form_web(): void
    {
        $user = $this->customer();
        $date = now()->addDays(3)->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 10, price: 50000);

        $response = $this->actingAs($user)->post(route('booking.store', $destination->slug), [
            'visit_date' => $date,
            'items' => [
                ['ticket_type_id' => $ticketType->id, 'quantity' => 2],
                ['ticket_type_id' => $ticketType->id, 'quantity' => 0],
            ],
            'customer_name' => 'Andi Pratama',
            'customer_phone' => '081299998888',
            'voucher_code' => '',
        ]);

        $booking = Booking::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('booking.show', $booking->code));

        $this->assertSame(Booking::STATUS_PENDING, $booking->status);
        $this->assertSame('100000.00', $booking->total_amount);
        $this->assertSame(2, $booking->items()->sum('quantity'));
        $this->assertDatabaseHas('ticket_availability', [
            'ticket_type_id' => $ticketType->id,
            'sold' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('booking.show', $booking->code))
            ->assertOk()
            ->assertSee($booking->code)
            ->assertSee('Andi Pratama')
            ->assertSee('100.000')
            ->assertSee('Metode Pembayaran');
    }

    public function test_booking_tanpa_tiket_tidak_dapat_dibuat(): void
    {
        $user = $this->customer();
        $date = now()->addDay()->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date);

        $this->actingAs($user)
            ->from(route('booking.create', $destination->slug))
            ->post(route('booking.store', $destination->slug), [
                'visit_date' => $date,
                'items' => [
                    ['ticket_type_id' => $ticketType->id, 'quantity' => 0],
                ],
            ])
            ->assertRedirect(route('booking.create', $destination->slug))
            ->assertSessionHasErrors('items');

        $this->assertSame(0, Booking::count());
    }

    public function test_alur_pembayaran_manual_sampai_e_ticket_diterbitkan(): void
    {
        Storage::fake('public');

        $user = $this->customer();
        $admin = $this->admin();
        [, , $booking] = $this->createBooking($user, 2);

        $this->actingAs($user)
            ->post(route('payment.store', $booking->code), ['method' => 'transfer'])
            ->assertRedirect();

        $payment = Payment::query()->latest('id')->firstOrFail();

        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame('transfer', $payment->method);
        $this->assertSame('100000.00', $payment->amount);

        $this->actingAs($user)
            ->get(route('payment.show', $payment->transaction_ref))
            ->assertOk()
            ->assertSee('Instruksi Transfer')
            ->assertSee('BCA')
            ->assertSee('Unggah Bukti Transfer')
            ->assertSee('100.000');

        $this->actingAs($user)
            ->post(route('payment.proof', $payment->transaction_ref), [
                'proof' => UploadedFile::fake()->create('bukti-transfer.jpg', 100, 'image/jpeg'),
            ])
            ->assertRedirect(route('booking.status', $booking->code));

        $payment->refresh();

        $this->assertNotNull($payment->proof_path);
        Storage::disk('public')->assertExists($payment->proof_path);

        $this->actingAs($user)
            ->get(route('booking.status', $booking->code))
            ->assertOk()
            ->assertSee('Menunggu Verifikasi')
            ->assertSee($booking->code);

        $this->actingAs($admin)
            ->from(route('admin.payments.index'))
            ->post(route('admin.payments.verify', $payment->transaction_ref))
            ->assertRedirect(route('admin.payments.index'))
            ->assertSessionHas('ok');

        $payment->refresh();
        $booking->refresh();

        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertNotNull($payment->verified_at);
        $this->assertSame($admin->id, $payment->verified_by);
        $this->assertSame(Booking::STATUS_PAID, $booking->status);
        $this->assertSame(2, $booking->tickets()->count());
        $this->assertSame(2, Ticket::query()->where('booking_id', $booking->id)->count());

        $ticket = $booking->tickets()->firstOrFail();

        $this->actingAs($user)
            ->get(route('ticket.show', $booking->code))
            ->assertOk()
            ->assertSee($booking->code)
            ->assertSee($ticket->code)
            ->assertSee('Unduh QR (SVG)')
            ->assertSee('<svg', false);

        $this->actingAs($user)
            ->get(route('ticket.qr', [$booking->code, $ticket->id]))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml')
            ->assertSee('<svg', false);

        $this->actingAs($this->customer())
            ->get(route('ticket.qr', [$booking->code, $ticket->id]))
            ->assertForbidden();
    }

    public function test_e_ticket_belum_tersedia_sebelum_booking_lunas(): void
    {
        $user = $this->customer();
        [, , $booking] = $this->createBooking($user);

        $this->actingAs($user)
            ->get(route('ticket.show', $booking->code))
            ->assertNotFound();
    }

    public function test_pengunjung_tidak_dapat_melihat_booking_atau_pembayaran_orang_lain(): void
    {
        Storage::fake('public');

        $owner = $this->customer();
        $other = $this->customer();
        [, , $booking] = $this->createBooking($owner);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'transaction_ref' => 'TRF-UJI-'.uniqid(),
            'method' => 'transfer',
            'amount' => $booking->total_amount,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->actingAs($other)->get(route('booking.show', $booking->code))->assertForbidden();
        $this->actingAs($other)->get(route('booking.status', $booking->code))->assertForbidden();
        $this->actingAs($other)->get(route('payment.show', $payment->transaction_ref))->assertForbidden();
        $this->actingAs($other)->get(route('ticket.show', $booking->code))->assertForbidden();
        $this->actingAs($other)->post(route('booking.cancel', $booking->code))->assertForbidden();
    }

    public function test_pengunjung_dapat_membatalkan_booking_dan_kuota_kembali(): void
    {
        $user = $this->customer();
        [, $ticketType, $booking] = $this->createBooking($user, 3);

        $this->actingAs($user)
            ->post(route('booking.cancel', $booking->code))
            ->assertRedirect(route('booking.show', $booking->code));

        $this->assertSame(Booking::STATUS_CANCELLED, $booking->fresh()->status);
        $this->assertDatabaseHas('ticket_availability', [
            'ticket_type_id' => $ticketType->id,
            'sold' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('booking.show', $booking->code))
            ->assertOk()
            ->assertSee('dibatalkan');
    }

    public function test_booking_yang_kedaluwarsa_diproses_saat_halaman_dibuka(): void
    {
        $user = $this->customer();
        [, $ticketType, $booking] = $this->createBooking($user, 2);

        $booking->update(['expires_at' => now()->subMinute()]);

        $this->actingAs($user)
            ->get(route('booking.show', $booking->code))
            ->assertOk()
            ->assertSee('kedaluwarsa');

        $this->assertSame(Booking::STATUS_EXPIRED, $booking->fresh()->status);
        $this->assertDatabaseHas('ticket_availability', [
            'ticket_type_id' => $ticketType->id,
            'sold' => 0,
        ]);
    }

    public function test_bukti_transfer_ditolak_setelah_batas_waktu_pembayaran(): void
    {
        Storage::fake('public');

        $user = $this->customer();
        [, $ticketType, $booking] = $this->createBooking($user, 2);

        $this->actingAs($user)
            ->post(route('payment.store', $booking->code), ['method' => 'transfer']);

        $payment = Payment::query()->latest('id')->firstOrFail();

        $booking->update(['expires_at' => now()->subMinute()]);

        $this->actingAs($user)
            ->from(route('payment.show', $payment->transaction_ref))
            ->post(route('payment.proof', $payment->transaction_ref), [
                'proof' => UploadedFile::fake()->create('bukti-transfer.jpg', 100, 'image/jpeg'),
            ])
            ->assertRedirect(route('payment.show', $payment->transaction_ref))
            ->assertSessionHasErrors('payment');

        $this->assertSame(Booking::STATUS_EXPIRED, $booking->fresh()->status);
        $this->assertSame(Payment::STATUS_EXPIRED, $payment->fresh()->status);
        $this->assertNull($payment->fresh()->proof_path);
        $this->assertDatabaseHas('ticket_availability', [
            'ticket_type_id' => $ticketType->id,
            'sold' => 0,
        ]);
    }

    public function test_admin_tidak_dapat_memverifikasi_pembayaran_booking_kedaluwarsa(): void
    {
        Storage::fake('public');

        $user = $this->customer();
        $admin = $this->admin();
        [, , $booking] = $this->createBooking($user, 2);

        $this->actingAs($user)
            ->post(route('payment.store', $booking->code), ['method' => 'transfer']);

        $payment = Payment::query()->latest('id')->firstOrFail();

        $this->actingAs($user)
            ->post(route('payment.proof', $payment->transaction_ref), [
                'proof' => UploadedFile::fake()->create('bukti-transfer.jpg', 100, 'image/jpeg'),
            ]);

        $booking->update(['expires_at' => now()->subMinute()]);

        $this->actingAs($admin)
            ->from(route('admin.payments.index'))
            ->post(route('admin.payments.verify', $payment->transaction_ref))
            ->assertRedirect(route('admin.payments.index'))
            ->assertSessionHasErrors('payment');

        $this->assertSame(Booking::STATUS_EXPIRED, $booking->fresh()->status);
        $this->assertSame(Payment::STATUS_EXPIRED, $payment->fresh()->status);
        $this->assertNull($payment->fresh()->verified_at);
        $this->assertSame(0, $booking->tickets()->count());
    }

    public function test_riwayat_menampilkan_booking_sesuai_tab(): void
    {
        $user = $this->customer();
        [, , $booking] = $this->createBooking($user);

        $this->actingAs($user)
            ->get(route('booking.history'))
            ->assertOk()
            ->assertSee($booking->code)
            ->assertSee('Aktif');

        $this->actingAs($user)
            ->get(route('booking.history', ['tab' => 'selesai']))
            ->assertOk()
            ->assertSee('Belum ada booking pada tab ini.');
    }

    public function test_profil_dapat_diperbarui(): void
    {
        $user = $this->customer(['name' => 'Nama Lama']);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Nama Baru',
                'phone' => '081277776666',
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('081277776666', $user->phone);
    }

    public function test_pengunjung_dapat_memberi_ulasan_setelah_booking_lunas(): void
    {
        $user = $this->customer();
        [$destination, , $booking] = $this->createBooking($user);

        $booking->update([
            'status' => Booking::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('katalog.detail', $destination->slug))
            ->assertOk()
            ->assertSee('Berikan Ulasan');

        $this->actingAs($user)
            ->post(route('review.store', $destination->slug), [
                'rating' => 5,
                'comment' => 'Pengalaman yang menyenangkan.',
            ])
            ->assertRedirect(route('katalog.detail', $destination->slug))
            ->assertSessionHas('ok');

        $this->assertDatabaseHas('reviews', [
            'destination_id' => $destination->id,
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'Pengalaman yang menyenangkan.',
        ]);

        $this->actingAs($user)
            ->get(route('katalog.detail', $destination->slug))
            ->assertOk()
            ->assertSee('Pengalaman yang menyenangkan.')
            ->assertSee('Kamu sudah memberi ulasan')
            ->assertDontSee('Berikan Ulasan');

        $this->actingAs($user)
            ->post(route('review.store', $destination->slug), [
                'rating' => 4,
                'comment' => 'Ulasan kedua.',
            ])
            ->assertSessionHasErrors('rating');

        $this->assertSame(1, $destination->reviews()->count());
    }

    public function test_ulasan_ditolak_jika_booking_belum_lunas(): void
    {
        $user = $this->customer();
        [$destination] = $this->createBooking($user);

        $this->actingAs($user)
            ->post(route('review.store', $destination->slug), [
                'rating' => 5,
                'comment' => 'Belum bayar.',
            ])
            ->assertSessionHasErrors('review');

        $this->assertSame(0, $destination->reviews()->count());
    }

    public function test_admin_tidak_dapat_memberi_ulasan_lewat_halaman_pengunjung(): void
    {
        $admin = $this->admin();
        $date = now()->addDay()->toDateString();
        [$destination] = $this->destinationWithTicket($date);

        $this->actingAs($admin)
            ->post(route('review.store', $destination->slug), ['rating' => 5])
            ->assertForbidden();
    }
}
