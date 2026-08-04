# MOE-Laravel-Auth

Paket autentikasi lengkap untuk ekosistem MOE Laravel: login, registrasi, OTP multi-channel (Email / WhatsApp / SMS), Google OAuth, reset password, dan middleware role â€” semuanya berbasis Livewire.

## Instalasi

```bash
composer require moe/laravel-auth:dev-main-main
```

Publish aset yang dibutuhkan:

```bash
php artisan vendor:publish --provider="Moe\Auth\MoeAuthServiceProvider" --tag=moe-auth-config
php artisan vendor:publish --provider="Moe\Auth\MoeAuthServiceProvider" --tag=moe-auth-migrations
php artisan vendor:publish --provider="Moe\Auth\MoeAuthServiceProvider" --tag=moe-auth-views
php artisan vendor:publish --provider="Moe\Auth\MoeAuthServiceProvider" --tag=moe-auth-translations
php artisan migrate
```

## Yang Termasuk

| Komponen | Keterangan |
|---|---|
| `Login` / `Register` | Komponen Livewire untuk login & registrasi |
| `ForgotPassword` / `ResetPassword` | Alur reset password via email |
| `GoogleCallback` + `GoogleAuthController` | Login via Google OAuth (membutuhkan `laravel/socialite`) |
| `OtpService` | OTP multi-channel: `EmailChannel`, `WhatsAppChannel`, `SmsChannel` |
| `OtpCode` (model) + `OtpMail` | Penyimpanan & pengiriman kode OTP |
| `RequireRole` middleware | Proteksi route berdasarkan role |
| `LoginAction` / `RegisterAction` | Action class yang dapat dipakai ulang di luar Livewire |

## Penggunaan

### Middleware role

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // route khusus admin
});
```

Daftarkan alias middleware `role` ke `Moe\Auth\Middleware\RequireRole` di bootstrap aplikasi.

### OTP

```php
use Moe\Auth\Services\OtpService;

$otp = app(OtpService::class);
$otp->send($user, channel: 'email');   // email | whatsapp | sms
$otp->verify($user, $code);
```

Channel WhatsApp/SMS membutuhkan konfigurasi gateway pada file `config/moe-auth.php` hasil publish.

## Dependensi

- PHP `^8.2`, Illuminate `^11.0 | ^12.0 | ^13.0`
- Disarankan: `laravel/socialite` (Google OAuth), `moe/laravel-settings`
