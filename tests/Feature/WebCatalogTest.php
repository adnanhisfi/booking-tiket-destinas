<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class WebCatalogTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    public function test_filter_lokasi_dan_rentang_harga_di_halaman_destinasi(): void
    {
        $date = now()->addDay()->toDateString();
        [$murah] = $this->destinationWithTicket($date, price: 50000);

        $mahal = Destination::create([
            'category_id' => $murah->category_id,
            'name' => 'Danau Uji',
            'slug' => 'danau-uji-'.uniqid(),
            'description' => 'Destinasi mahal untuk pengujian.',
            'location' => 'Bandung',
            'status' => Destination::STATUS_ACTIVE,
        ]);

        TicketType::create([
            'destination_id' => $mahal->id,
            'name' => 'Tiket Premium',
            'price' => 500000,
            'default_quota' => 10,
            'status' => TicketType::STATUS_ACTIVE,
        ]);

        $this->get('/destinasi?location=Bandung')
            ->assertOk()
            ->assertSee('Danau Uji')
            ->assertDontSee('Pantai Uji');

        $this->get('/destinasi?min_price=100000')
            ->assertOk()
            ->assertSee('Danau Uji')
            ->assertDontSee('Pantai Uji');

        $this->get('/destinasi?max_price=50000')
            ->assertOk()
            ->assertSee('Pantai Uji')
            ->assertDontSee('Danau Uji');

        $this->get('/destinasi?sort=price_asc')
            ->assertOk()
            ->assertSeeInOrder(['Pantai Uji', 'Danau Uji']);
    }
}
