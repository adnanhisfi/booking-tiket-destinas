<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use App\Models\User;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class WebAdminTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    /**
     * @return array{0: Destination, 1: TicketType, 2: Booking, 3: Payment}
     */
    protected function paidBooking(User $user, ?string $date = null, int $quantity = 1): array
    {
        $date ??= now()->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 10);

        $booking = app(BookingService::class)->create($user, [
            'destination_id' => $destination->id,
            'visit_date' => $date,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => $quantity]],
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'transaction_ref' => 'TRF-ADMIN-'.uniqid(),
            'method' => 'transfer',
            'amount' => $booking->total_amount,
            'status' => Payment::STATUS_PENDING,
            'proof_path' => 'payment-proofs/bukti-uji.jpg',
        ]);

        app(PaymentService::class)->verifyManual($payment, $this->admin());

        return [$destination, $ticketType, $booking->fresh(), $payment->fresh()];
    }

    public function test_seluruh_halaman_admin_utama_dapat_dibuka(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        [, , $booking] = $this->paidBooking($customer);

        $pages = [
            route('admin.dashboard'),
            route('admin.destinations.index'),
            route('admin.destinations.create'),
            route('admin.tickets.index'),
            route('admin.tickets.create'),
            route('admin.quotas.index'),
            route('admin.bookings.index'),
            route('admin.bookings.show', $booking->code),
            route('admin.payments.index'),
            route('admin.payments.index', ['awaiting_verification' => 1]),
            route('admin.reports.index'),
            route('admin.reports.index', ['periode' => 7]),
            route('admin.reports.index', ['periode' => 365]),
            route('admin.scan.index'),
        ];

        foreach ($pages as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_halaman_scan_menyediakan_pemindai_kamera_dan_input_manual(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.scan.index'))
            ->assertOk()
            ->assertSee('Scan dengan Kamera')
            ->assertSee('html5-qrcode')
            ->assertSee('Validasi Kode Tiket');
    }

    public function test_admin_dapat_menambah_mengubah_dan_menghapus_destinasi(): void
    {
        $admin = $this->admin();
        $category = Category::create(['name' => 'Danau', 'slug' => 'danau-'.uniqid()]);

        $this->actingAs($admin)
            ->post(route('admin.destinations.store'), [
                'name' => 'Danau Uji',
                'category_id' => $category->id,
                'description' => 'Danau buatan untuk pengujian.',
                'location' => 'Bandung',
                'address' => 'Jl. Uji No. 1',
                'opening_time' => '08:00',
                'closing_time' => '17:00',
                'facilities' => 'Parkir, Toilet, Mushola',
                'is_featured' => '1',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.destinations.index'))
            ->assertSessionHas('ok');

        $destination = Destination::query()->where('name', 'Danau Uji')->firstOrFail();

        $this->assertSame(['Parkir', 'Toilet', 'Mushola'], $destination->facilities);
        $this->assertTrue($destination->is_featured);
        $this->assertSame('danau-uji', $destination->slug);

        $this->actingAs($admin)
            ->put(route('admin.destinations.update', $destination->slug), [
                'name' => 'Danau Uji Baru',
                'category_id' => $category->id,
                'description' => 'Deskripsi diperbarui.',
                'location' => 'Bandung Barat',
                'facilities' => 'Parkir, Toilet',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('admin.destinations.index'));

        $destination->refresh();

        $this->assertSame('Danau Uji Baru', $destination->name);
        $this->assertSame(Destination::STATUS_INACTIVE, $destination->status);
        $this->assertSame('danau-uji-baru', $destination->slug);

        $this->actingAs($admin)
            ->delete(route('admin.destinations.destroy', $destination->slug))
            ->assertRedirect(route('admin.destinations.index'));

        $this->assertDatabaseMissing('destinations', ['id' => $destination->id]);
    }

    public function test_thumbnail_yang_diunggah_tersimpan_dan_tampil_di_halaman_admin(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $category = Category::create(['name' => 'Pantai', 'slug' => 'pantai-'.uniqid()]);

        $thumbnail = UploadedFile::fake()->createWithContent(
            'pantai.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')
        );

        $this->actingAs($admin)
            ->post(route('admin.destinations.store'), [
                'name' => 'Pantai Uji',
                'category_id' => $category->id,
                'description' => 'Pantai untuk pengujian unggah gambar.',
                'location' => 'Bali',
                'status' => 'active',
                'thumbnail' => $thumbnail,
            ])
            ->assertRedirect(route('admin.destinations.index'));

        $destination = Destination::query()->where('name', 'Pantai Uji')->firstOrFail();

        $this->assertNotNull($destination->thumbnail);
        Storage::disk('public')->assertExists($destination->thumbnail);
        $this->assertSame(asset('storage/'.$destination->thumbnail), $destination->thumbnailUrl());

        $this->actingAs($admin)
            ->get(route('admin.destinations.index'))
            ->assertOk()
            ->assertSee($destination->thumbnailUrl(), false);
    }

    public function test_admin_dapat_mengelola_jenis_tiket_dan_kuota(): void
    {
        $admin = $this->admin();
        [$destination] = $this->destinationWithTicket(now()->addDay()->toDateString());

        $this->actingAs($admin)
            ->post(route('admin.tickets.store'), [
                'destination_id' => $destination->id,
                'name' => 'Tiket Anak',
                'description' => 'Untuk anak usia 3-12 tahun.',
                'price' => 30000,
                'default_quota' => 50,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.tickets.index'));

        $ticketType = TicketType::query()->where('name', 'Tiket Anak')->firstOrFail();

        $this->assertSame('30000.00', $ticketType->price);

        $this->actingAs($admin)
            ->put(route('admin.tickets.update', $ticketType->id), [
                'destination_id' => $destination->id,
                'name' => 'Tiket Anak Diperbarui',
                'price' => 35000,
                'default_quota' => 40,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.tickets.index'));

        $this->assertSame('Tiket Anak Diperbarui', $ticketType->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('admin.tickets.destroy', $ticketType->id))
            ->assertRedirect(route('admin.tickets.index'));

        $this->assertDatabaseMissing('ticket_types', ['id' => $ticketType->id]);

        $date = now()->addDays(4)->toDateString();

        $this->actingAs($admin)
            ->post(route('admin.quotas.store'), [
                'ticket_type_id' => TicketType::query()->where('destination_id', $destination->id)->value('id'),
                'visit_date' => $date,
                'quota' => 25,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ticket_availability', [
            'visit_date' => $date,
            'quota' => 25,
            'sold' => 0,
        ]);
    }

    public function test_kuota_tidak_boleh_diatur_di_bawah_jumlah_terjual(): void
    {
        $admin = $this->admin();
        $date = now()->addDay()->toDateString();
        [, $ticketType] = $this->destinationWithTicket($date, quota: 10);

        TicketAvailability::query()
            ->where('ticket_type_id', $ticketType->id)
            ->update(['sold' => 6]);

        $this->actingAs($admin)
            ->from(route('admin.quotas.index'))
            ->post(route('admin.quotas.store'), [
                'ticket_type_id' => $ticketType->id,
                'visit_date' => $date,
                'quota' => 3,
            ])
            ->assertRedirect(route('admin.quotas.index'))
            ->assertSessionHasErrors('quota');

        $this->assertSame(10, TicketAvailability::query()
            ->where('ticket_type_id', $ticketType->id)
            ->value('quota'));
    }

    public function test_admin_dapat_menyelesaikan_dan_membatalkan_booking(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        [, , $paid] = $this->paidBooking($customer);

        $this->actingAs($admin)
            ->get(route('admin.bookings.show', $paid->code))
            ->assertOk()
            ->assertSee($paid->code)
            ->assertSee($customer->name);

        $this->actingAs($admin)
            ->from(route('admin.bookings.show', $paid->code))
            ->post(route('admin.bookings.complete', $paid->code))
            ->assertRedirect(route('admin.bookings.show', $paid->code));

        $this->assertSame(Booking::STATUS_COMPLETED, $paid->fresh()->status);

        $date = now()->addDays(5)->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 5);
        $pending = app(BookingService::class)->create($customer, [
            'destination_id' => $destination->id,
            'visit_date' => $date,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 2]],
        ]);

        $this->actingAs($admin)
            ->from(route('admin.bookings.show', $pending->code))
            ->post(route('admin.bookings.cancel', $pending->code))
            ->assertRedirect(route('admin.bookings.show', $pending->code));

        $this->assertSame(Booking::STATUS_CANCELLED, $pending->fresh()->status);
        $this->assertSame('Dibatalkan oleh admin.', $pending->fresh()->notes);
        $this->assertDatabaseHas('ticket_availability', [
            'ticket_type_id' => $ticketType->id,
            'sold' => 0,
        ]);
    }

    public function test_pembayaran_dapat_difilter_berdasarkan_kata_kunci_dan_menunggu_verifikasi(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();

        [, , $paidBooking, $paidPayment] = $this->paidBooking($customer);

        $date = now()->addDays(4)->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date);
        $pendingBooking = app(BookingService::class)->create($customer, [
            'destination_id' => $destination->id,
            'visit_date' => $date,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 1]],
        ]);
        $pendingPayment = Payment::create([
            'booking_id' => $pendingBooking->id,
            'transaction_ref' => 'TRF-CARI-'.uniqid(),
            'method' => 'qris',
            'amount' => $pendingBooking->total_amount,
            'status' => Payment::STATUS_PENDING,
            'proof_path' => 'payment-proofs/cari.jpg',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.payments.index', ['q' => $paidBooking->code]))
            ->assertOk()
            ->assertSee($paidPayment->transaction_ref)
            ->assertDontSee($pendingPayment->transaction_ref);

        $this->actingAs($admin)
            ->get(route('admin.payments.index', ['awaiting_verification' => 1]))
            ->assertOk()
            ->assertSee($pendingPayment->transaction_ref)
            ->assertDontSee($paidPayment->transaction_ref);
    }

    public function test_scan_tiket_valid_dan_penolakan_scan_ulang(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        [, , $booking] = $this->paidBooking($customer, now()->toDateString(), 1);

        $ticket = $booking->tickets()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.scan.validate'), ['code' => 'JLK:'.$ticket->code])
            ->assertOk()
            ->assertSee('Tiket Valid')
            ->assertSee($ticket->code);

        $this->assertSame(Ticket::STATUS_USED, $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->used_at);
        $this->assertSame($admin->id, $ticket->fresh()->validated_by);

        $this->actingAs($admin)
            ->post(route('admin.scan.validate'), ['code' => $ticket->code])
            ->assertOk()
            ->assertSee('Tiket Tidak Valid')
            ->assertSee('sudah dipakai');
    }

    public function test_scan_tiket_tanggal_salah_dan_kode_tidak_dikenal_ditolak(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        [, , $booking] = $this->paidBooking($customer, now()->addDay()->toDateString());

        $ticket = $booking->tickets()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.scan.validate'), ['code' => $ticket->code])
            ->assertOk()
            ->assertSee('Tiket Tidak Valid')
            ->assertSee('tanggal kunjungan');

        $this->actingAs($admin)
            ->post(route('admin.scan.validate'), ['code' => 'KODE-PALSU'])
            ->assertOk()
            ->assertSee('Tiket Tidak Valid')
            ->assertSee('tidak ditemukan');

        $this->assertSame(Ticket::STATUS_VALID, $ticket->fresh()->status);
    }

    public function test_filter_tanggal_tidak_valid_pada_laporan_tidak_menimbulkan_error(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['from' => 'abc', 'to' => 'xyz']))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['from' => ['abc']]))
            ->assertOk();
    }

    public function test_laporan_dan_ekspor_csv_berfungsi(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        [$destination, , $booking] = $this->paidBooking($customer);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'periode' => 7,
                'destination_id' => $destination->id,
            ]))
            ->assertOk()
            ->assertSee($destination->name)
            ->assertSee(number_format((float) $booking->total_amount, 0, ',', '.'));

        $export = $this->actingAs($admin)->get(route('admin.bookings.export'));

        $export->assertOk();
        $this->assertStringContainsString('Kode', $export->streamedContent());
        $this->assertStringContainsString($booking->code, $export->streamedContent());

        $reportExport = $this->actingAs($admin)->get(route('admin.reports.export', [
            'from' => now()->subDays(7)->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $reportExport->assertOk();
        $this->assertStringContainsString('Kode', $reportExport->streamedContent());
    }
}
