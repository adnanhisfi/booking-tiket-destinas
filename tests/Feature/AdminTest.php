<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Destination;
use App\Models\TicketAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    public function test_admin_dapat_membuat_destinasi_dan_jenis_tiket(): void
    {
        $admin = $this->admin();

        $categoryId = Category::create(['name' => 'Pantai', 'slug' => 'pantai-'.uniqid()])->id;

        $destinationId = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/destinations', [
                'category_id' => $categoryId,
                'name' => 'Pantai Baru',
                'description' => 'Deskripsi destinasi baru.',
                'location' => 'Bali',
                'status' => 'active',
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/tickets', [
                'destination_id' => $destinationId,
                'name' => 'Tiket Masuk',
                'price' => 25000,
                'default_quota' => 100,
            ])
            ->assertCreated()
            ->assertJsonPath('data.price', 25000);
    }

    public function test_admin_dapat_mengatur_kuota_per_tanggal(): void
    {
        $admin = $this->admin();
        $date = now()->addDays(5)->toDateString();
        [, $ticketType] = $this->destinationWithTicket($date, quota: 10);

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/admin/availability', [
                'items' => [
                    ['ticket_type_id' => $ticketType->id, 'visit_date' => $date, 'quota' => 250],
                ],
            ])
            ->assertOk();

        $this->assertSame(250, TicketAvailability::where('ticket_type_id', $ticketType->id)->value('quota'));
    }

    public function test_kuota_tidak_boleh_lebih_kecil_dari_yang_terjual(): void
    {
        $admin = $this->admin();
        $date = now()->addDays(5)->toDateString();
        [, $ticketType] = $this->destinationWithTicket($date, quota: 10);

        $ticketType->availabilities()->update(['sold' => 5]);

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/admin/availability', [
                'items' => [
                    ['ticket_type_id' => $ticketType->id, 'visit_date' => $date, 'quota' => 3],
                ],
            ])
            ->assertStatus(422);
    }

    public function test_admin_mendapatkan_dashboard_dan_laporan_penjualan(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonStructure(['data' => ['today', 'totals']]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/reports/sales')
            ->assertOk()
            ->assertJsonStructure(['data' => ['summary', 'daily', 'by_destination']]);
    }

    public function test_katalog_publik_menyembunyikan_destinasi_nonaktif(): void
    {
        $date = now()->addDay()->toDateString();
        [$destination] = $this->destinationWithTicket($date);
        $destination->update(['status' => Destination::STATUS_INACTIVE]);

        $this->getJson('/api/v1/destinations')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_ketersediaan_publik_menampilkan_sisa_kuota(): void
    {
        $date = now()->addDays(3)->toDateString();
        [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 10);
        $ticketType->availabilities()->update(['sold' => 4]);

        $this->getJson("/api/v1/destinations/{$destination->id}/tickets?date={$date}")
            ->assertOk()
            ->assertJsonPath('data.ticket_types.0.remaining', 6)
            ->assertJsonPath('data.ticket_types.0.available', true);
    }
}
