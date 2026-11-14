<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use CreatesCatalog, RefreshDatabase;

    public function test_endpoint_api_tidak_dikenal_mengembalikan_json_404_konsisten(): void
    {
        $this->getJson('/api/v1/endpoint-tidak-ada')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 404)
            ->assertJsonStructure(['success', 'message', 'code']);
    }

    public function test_request_api_tanpa_token_mengembalikan_json_401_konsisten(): void
    {
        $this->getJson('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 401);
    }

    public function test_validasi_api_mengembalikan_422_dengan_detail_errors(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 422)
            ->assertJsonStructure(['message', 'errors' => ['email', 'password']]);
    }

    public function test_request_api_dengan_metode_salah_mengembalikan_json_405(): void
    {
        $this->deleteJson('/api/v1/categories')
            ->assertStatus(405)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 405);
    }

    public function test_halaman_web_404_memakai_halaman_error_kustom(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('404')
            ->assertSee('Halaman Tidak Ditemukan')
            ->assertSee('Kembali ke Beranda');
    }

    public function test_halaman_web_403_memakai_halaman_error_kustom(): void
    {
        $this->actingAs($this->customer())
            ->get('/admin')
            ->assertForbidden()
            ->assertSee('403')
            ->assertSee('Akses Ditolak');
    }
}
