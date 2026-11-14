<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class PerformanceUsabilityTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    public function test_daftar_kategori_di_cache_dan_dibersihkan_saat_kategori_berubah(): void
    {
        $this->get('/')->assertOk();

        $this->assertTrue(Cache::has(Category::CACHE_KEY));

        Category::create([
            'name' => 'Kategori Baru',
            'slug' => 'kategori-baru-'.uniqid(),
        ]);

        $this->assertFalse(Cache::has(Category::CACHE_KEY));
    }

    public function test_request_kedua_tidak_mengulang_query_kategori(): void
    {
        $this->get('/')->assertOk();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get('/')->assertOk();

        $categoryQueries = collect(DB::getQueryLog())
            ->filter(fn (array $query) => str_contains(strtolower($query['query']), 'from "categories"'))
            ->count();

        $this->assertSame(0, $categoryQueries, 'Daftar kategori seharusnya diambil dari cache pada request kedua.');
    }

    public function test_detail_destinasi_menampilkan_rating_dan_jumlah_ulasan_hasil_agregasi(): void
    {
        $user = $this->customer();
        [$destination] = $this->destinationWithTicket(now()->toDateString());

        Review::create([
            'destination_id' => $destination->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'Tempatnya bagus dan bersih.',
        ]);

        $this->get(route('katalog.detail', $destination->slug))
            ->assertOk()
            ->assertSee('4.0')
            ->assertSee('1 ulasan');
    }

    public function test_gambar_katalog_memakai_lazy_loading_untuk_hemat_bandwidth(): void
    {
        $this->destinationWithTicket(now()->toDateString());

        $this->get('/')
            ->assertOk()
            ->assertSee('loading="lazy"', false)
            ->assertSee('decoding="async"', false);
    }
}
