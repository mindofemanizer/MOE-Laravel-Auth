<?php

namespace Moe\Auth\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Moe\Auth\Services\GoogleService;

class GoogleAuthController
{
    public function redirect(GoogleService $google): RedirectResponse
    {
        return $google->redirect();
    }

    public function callback(GoogleService $google): RedirectResponse
    {
        $user = $google->handleCallback();

        if ($user) {
            // Simpan foto Google profile jika belum ada avatar
            if (blank($user->getRawOriginal('avatar'))) {
                try {
                    $socialUser = $google->getSocialUser();
                    $googleAvatarUrl = $socialUser?->getAvatar();
                    if (! empty($googleAvatarUrl) && str_starts_with($googleAvatarUrl, 'http')) {
                        $this->saveGoogleAvatar($user, $googleAvatarUrl);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Google avatar save failed: '.$e->getMessage());
                }
            }

            Auth::login($user, remember: true);
            session()->regenerate();

            return redirect(config('moe-auth.redirects.login', '/dashboard'));
        }

        return redirect()->route('login')->with('error', 'Google authentication failed.');
    }

    protected function saveGoogleAvatar($user, string $googleAvatarUrl): void
    {
        $identifier = $user->customer_code ?: $user->id;
        $disk = config('filesystems.default');

        try {
            $response = Http::timeout(10)->get($googleAvatarUrl);

            if ($response->successful() && $response->body() !== '') {
                $mime = $response->header('Content-Type') ?: 'image/jpeg';
                $ext = match (true) {
                    str_contains($mime, 'png') => 'png',
                    str_contains($mime, 'webp') => 'webp',
                    default => 'jpg',
                };

                $path = "avatars/customers/{$identifier}/google_avatar.{$ext}";
                Storage::disk($disk)->put($path, $response->body());
                $user->update(['avatar' => $path]);

                return;
            }
        } catch (\Throwable) {
            // fallthrough
        }

        $user->update(['avatar' => $googleAvatarUrl]);
    }
}
