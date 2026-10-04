<?php

namespace Moe\Auth\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Moe\Auth\Middleware\RequireRole;
use Moe\Auth\Tests\TestCase;
use Moe\Auth\Tests\User;

class RequireRoleTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('moe-auth.roles.portals', [
            'admin' => ['admin', 'super_admin'],
        ]);
        $app['config']->set('moe-auth.roles.redirects', [
            'login' => '/login',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/dash', fn () => 'ok')
            ->middleware(RequireRole::class.':admin')
            ->name('dashboard');

        Route::middleware('web')->get('/api/protected', fn () => 'ok')
            ->middleware(RequireRole::class.':admin')
            ->name('api.protected');
    }

    private function makeUser(?string $role): User
    {
        $user = new User();
        $user->name = 'U';
        $user->email = 'u'.uniqid().'@example.com';
        $user->password = 'secret123';
        $user->role = $role;
        $user->save();

        return $user;
    }

    public function test_role_cocok_diizinkan(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get('/dash')
            ->assertOk()
            ->assertSee('ok');
    }

    public function test_role_tidak_cocok_ditolak(): void
    {
        // Web: harus redirect (bukan 403) agar perilaku lama tidak berubah.
        $this->actingAs($this->makeUser('client'))
            ->get('/dash')
            ->assertRedirect();
    }

    public function test_belum_login_di_web_di_redirect(): void
    {
        $this->get('/dash')->assertRedirect();
    }

    public function test_permintaan_api_json_dapat_401(): void
    {
        $this->getJson('/api/protected')
            ->assertStatus(401)
            ->assertJson(['message' => 'Tidak terautentikasi.']);
    }

    public function test_permintaan_api_json_dapat_403_bila_role_salah(): void
    {
        $this->actingAs($this->makeUser('client'))
            ->getJson('/api/protected')
            ->assertStatus(403)
            ->assertJson(['message' => 'Akses ditolak.']);
    }

    public function test_permintaan_api_json_lolos_bila_role_cocok(): void
    {
        $this->actingAs($this->makeUser('super_admin'))
            ->getJson('/api/protected')
            ->assertOk()
            ->assertSee('ok');
    }
}
