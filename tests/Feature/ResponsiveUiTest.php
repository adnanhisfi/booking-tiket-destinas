<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class ResponsiveUiTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    public function test_halaman_pengunjung_memakai_viewport_dan_navigasi_bawah_mobile(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('name="viewport"', false)
            ->assertSee('fixed bottom-0 inset-x-0 max-w-md', false)
            ->assertSee('Navigasi utama')
            ->assertSee('Lewati ke konten utama');
    }

    public function test_layout_admin_memakai_sidebar_offscreen_dan_tombol_menu_mobile(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('sidebarOpen', false)
            ->assertSee('Buka menu admin')
            ->assertSee('lg:ml-64', false)
            ->assertSee('overflow-x-auto', false)
            ->assertSee('grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4', false);
    }

    public function test_tabel_dan_filter_admin_tetap_dapat_digunakan_di_layar_kecil(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/booking')
            ->assertOk()
            ->assertSee('overflow-x-auto', false)
            ->assertSee('min-w-[820px]', false)
            ->assertSee('w-full border rounded-xl px-4 py-2 sm:w-60', false);
    }

    public function test_form_admin_menumpuk_satu_kolom_pada_mobile(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/destinasi/create')
            ->assertOk()
            ->assertSee('md:grid-cols-2', false)
            ->assertSee('md:col-span-2', false);
    }
}
