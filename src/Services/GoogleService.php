<?php

namespace Moe\Auth\Services;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Moe\Auth\Models\User;

class GoogleService
{
    public function redirect()
    {
        if (! class_exists(Socialite::class)) {
            abort(500, 'laravel/socialite is required for Google OAuth.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleCallback(): ?User
    {
        if (! class_exists(Socialite::class)) {
            abort(500, 'laravel/socialite is required for Google OAuth.');
        }

        $socialUser = Socialite::driver('google')->user();

        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Update google_id if not set
            if (! $user->google_id) {
                $user->update(['google_id' => $socialUser->getId()]);
            }
        } else {
            // Create new user
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getEmail(),
                'email' => $socialUser->getEmail(),
                'google_id' => $socialUser->getId(),
                'email_verified_at' => now(), // Google email is considered verified
                'password' => null, // No password for OAuth users
            ]);
        }

        Auth::login($user, remember: true);

        return $user;
    }
}
