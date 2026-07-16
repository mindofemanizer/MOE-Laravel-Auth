<?php

namespace Moe\Auth\Services;

use Illuminate\Database\Eloquent\Model;
use Laravel\Socialite\Facades\Socialite;

class GoogleService
{
    public function redirect()
    {
        if (! class_exists(Socialite::class)) {
            abort(500, 'laravel/socialite is required for Google OAuth.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleCallback(): ?Model
    {
        if (! class_exists(Socialite::class)) {
            abort(500, 'laravel/socialite is required for Google OAuth.');
        }

        $socialUser = Socialite::driver('google')->user();

        $userModelClass = config('moe-auth.user_model', config('auth.providers.users.model'));

        if (! $userModelClass || ! class_exists($userModelClass)) {
            abort(500, 'User model not configured. Set moe-auth.user_model or auth.providers.users.model.');
        }

        $user = $userModelClass::where('email', $socialUser->getEmail())->first();

        if ($user) {
            if (! $user->google_id) {
                $user->update(['google_id' => $socialUser->getId()]);
            }
        } else {
            $user = $userModelClass::create([
                'name' => $socialUser->getName() ?? $socialUser->getEmail(),
                'email' => $socialUser->getEmail(),
                'google_id' => $socialUser->getId(),
                'email_verified_at' => now(),
                'password' => null,
            ]);
        }

        return $user;
    }
}
