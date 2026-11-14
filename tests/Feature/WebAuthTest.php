<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    public function test_halaman_login_pengunjung_dan_admin_dapat_dibuka(): void
    {
        $this->get('/masuk')
            ->assertOk()
            ->assertSee('Selamat Datang')
            ->assertSee('Masuk');

        $this->get('/admin/login')
            ->assertOk()
            ->assertHeader('Pragma', 'no-cache')
            ->assertSee('Masuk Admin');
    }

    public function test_pengunjung_dapat_mendaftar_dan_langsung_masuk(): void
    {
        $this->post('/daftar', [
            'name' => 'Sari Dewi',
            'email' => 'sari@example.test',
            'phone' => '081234567890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('katalog.beranda'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'sari@example.test',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->get('/profil')
            ->assertOk()
            ->assertSee('Sari Dewi');
    }

    public function test_pengunjung_dapat_login_dengan_email_maupun_nomor_hp(): void
    {
        $this->customer([
            'email' => 'budi@example.test',
            'phone' => '081200001111',
        ]);

        $this->post('/masuk', [
            'email' => 'budi@example.test',
            'password' => 'password',
        ])->assertRedirect(route('katalog.beranda'));

        $this->assertAuthenticated();
        $this->post('/logout')->assertRedirect(route('katalog.beranda'));
        $this->assertGuest();

        $this->post('/masuk', [
            'email' => '081200001111',
            'password' => 'password',
        ])->assertRedirect(route('katalog.beranda'));

        $this->assertAuthenticated();
    }

    public function test_password_salah_ditolak_di_halaman_login(): void
    {
        $this->customer(['email' => 'salah@example.test']);

        $this->from('/masuk')
            ->post('/masuk', [
                'email' => 'salah@example.test',
                'password' => 'password-salah',
            ])
            ->assertRedirect('/masuk')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_dapat_login_dan_membuka_panel(): void
    {
        $this->admin(['email' => 'admin-web@example.test']);

        $this->withSession(['url.intended' => route('booking.history')])
            ->post('/admin/login', [
                'email' => 'admin-web@example.test',
                'password' => 'password',
            ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_akun_pengunjung_tidak_bisa_login_lewat_form_admin(): void
    {
        $this->customer(['email' => 'bukan-admin@example.test']);

        $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'bukan-admin@example.test',
                'password' => 'password',
            ])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_tamu_diarahkan_ke_halaman_login_yang_sesuai(): void
    {
        $this->get('/riwayat')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/booking')->assertRedirect(route('admin.login'));
    }

    public function test_hak_akses_peran_ditegakkan_di_halaman_web(): void
    {
        $customer = $this->customer();
        $admin = $this->admin();

        $this->actingAs($customer)->get('/admin')->assertForbidden();
        $this->actingAs($admin)->get('/riwayat')->assertForbidden();
    }
}
