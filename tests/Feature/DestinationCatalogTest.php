<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Review;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class DestinationCatalogTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    public function test_pencarian_dan_filter_kategori_bekerja(): void
    {
        $date = now()->addDay()->toDateString();
        [$destination] = $this->destinationWithTicket($date);
        $categorySlug = $destination->category->slug;

        $this->getJson('/api/v1/destinations?q=Pantai')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/v1/destinations?q=TidakAda')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/api/v1/destinations?category='.$categorySlug)
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_urut_harga_dan_rentang_harga(): void
    {
        $category = Category::create(['name' => 'Pantai', 'slug' => 'pantai-'.uniqid()]);

        $murah = Destination::create([
            'category_id' => $category->id, 'name' => 'Destinasi Murah',
            'slug' => 'murah-'.uniqid(), 'description' => 'murah', 'location' => 'Bali', 'status' => 'active',
        ]);
        TicketType::create([
            'destination_id' => $murah->id, 'name' => 'Tiket', 'price' => 10000,
            'default_quota' => 10, 'status' => 'active',
        ]);

        $mahal = Destination::create([
            'category_id' => $category->id, 'name' => 'Destinasi Mahal',
            'slug' => 'mahal-'.uniqid(), 'description' => 'mahal', 'location' => 'Bali', 'status' => 'active',
        ]);
        TicketType::create([
            'destination_id' => $mahal->id, 'name' => 'Tiket', 'price' => 500000,
            'default_quota' => 10, 'status' => 'active',
        ]);

        $this->getJson('/api/v1/destinations?sort=price_asc')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Destinasi Murah');

        $this->getJson('/api/v1/destinations?sort=price_desc')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Destinasi Mahal');

        $this->getJson('/api/v1/destinations?min_price=100000')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Destinasi Mahal');
    }

    public function test_detail_destinasi_memuat_rating_dan_jenis_tiket(): void
    {
        $date = now()->addDay()->toDateString();
        [$destination] = $this->destinationWithTicket($date);

        $user = $this->customer();
        Review::create([
            'destination_id' => $destination->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'Bagus sekali',
        ]);

        $this->getJson("/api/v1/destinations/{$destination->id}")
            ->assertOk()
            ->assertJsonPath('data.ticket_types.0.name', 'Tiket Dewasa')
            ->assertJsonPath('data.reviews.0.rating', 5);
    }

    public function test_tanggal_di_luar_rentang_ditolak(): void
    {
        $date = now()->addDay()->toDateString();
        [$destination] = $this->destinationWithTicket($date);

        $this->getJson("/api/v1/destinations/{$destination->id}/tickets?date=".now()->subDay()->toDateString())
            ->assertStatus(422);
    }
}
