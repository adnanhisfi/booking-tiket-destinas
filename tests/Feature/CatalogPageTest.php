<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class CatalogPageTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    public function test_halaman_katalog_menampilkan_destinasi_dinamis(): void
    {
        $date = now()->addDay()->toDateString();
        [$destination] = $this->destinationWithTicket($date);

        $this->get('/')
            ->assertOk()
            ->assertSee('JelajahKu')
            ->assertSee($destination->name);
    }

    public function test_halaman_detail_destinasi_menampilkan_jenis_tiket(): void
    {
        $date = now()->addDay()->toDateString();
        [$destination] = $this->destinationWithTicket($date);

        $this->get('/destinasi/'.$destination->slug)
            ->assertOk()
            ->assertSee($destination->name)
            ->assertSee('Tiket Dewasa');
    }
}
