<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    public function test_pengunjung_dapat_registrasi_dan_login(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Siti',
            'email' => 'siti@example.test',
            'phone' => '08123',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated()->assertJsonPath('user.role', 'customer');

        $this->postJson('/api/v1/auth/login', [
            'email' => 'siti@example.test',
            'password' => 'password123',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_password_salah_ditolak(): void
    {
        $this->customer(['email' => 'budi@example.test', 'password' => 'password123']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'budi@example.test',
            'password' => 'salahbanget',
        ])->assertStatus(422);
    }

    public function test_admin_tidak_bisa_dibuat_lewat_form_publik(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Penyusup',
            'email' => 'penyusup@example.test',
            'phone' => '08123',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertCreated()->assertJsonPath('user.role', 'customer');
    }

    public function test_pengunjung_tidak_dapat_mengakses_checkout_tanpa_login(): void
    {
        $this->postJson('/api/v1/bookings', [])->assertUnauthorized();
    }

    public function test_pengunjung_tidak_dapat_mengakses_endpoint_admin(): void
    {
        $user = $this->customer();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/dashboard')
            ->assertForbidden();
    }
}
