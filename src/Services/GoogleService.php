<?php

namespace Moe\Auth\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleService
{
    public function redirect()
    {
        if (! class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            abort(500, 'laravel/socialite is required for Google OAuth.');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Resolve (and optionally create) a local account from Google OAuth.
     *
     * @param  class-string<Model>|null  $model
     * @param  array<string, mixed>  $createAttributes
     * @param  array<string, mixed>  $linkAttributes
     */
    public function handleCallback(
        ?string $model = null,
        ?bool $autoCreate = null,
        array $createAttributes = [],
        array $linkAttributes = [],
    ): ?Model {
        if (! class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            abort(500, 'laravel/socialite is required for Google OAuth.');
        }

        $socialUser = Socialite::driver('google')->user();

        $userModelClass = $model
            ?? config('moe-auth.user_model')
            ?? config('auth.providers.users.model');

        if (! $userModelClass || ! class_exists($userModelClass)) {
            abort(500, 'User model not configured. Set moe-auth.user_model or auth.providers.users.model.');
        }

        $autoCreate ??= (bool) config('moe-auth.google.auto_create', true);

        $placeholders = [
            '{id}' => $socialUser->getId(),
            '{name}' => $socialUser->getName() ?? $socialUser->getEmail(),
            '{email}' => $socialUser->getEmail(),
            '{avatar}' => $socialUser->getAvatar(),
        ];

        $user = $userModelClass::where('email', $socialUser->getEmail())->first();

        if ($user) {
            $defaults = config('moe-auth.google.link_attributes', ['google_id' => '{id}']);
            $attrs = $this->resolvePlaceholders(
                array_merge($defaults, $linkAttributes),
                $placeholders,
            );

            // Only write attributes that are empty/null on the existing model.
            $updates = [];
            foreach ($attrs as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                if (blank(data_get($user, $key))) {
                    $updates[$key] = $value;
                }
            }

            if ($updates !== []) {
                $user->update($updates);
            }

            return $user;
        }

        if (! $autoCreate) {
            return null;
        }

        return $userModelClass::create(array_merge([
            'name' => $socialUser->getName() ?? $socialUser->getEmail(),
            'email' => $socialUser->getEmail(),
            'google_id' => $socialUser->getId(),
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(40)),
        ], $this->resolvePlaceholders($createAttributes, $placeholders)));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $placeholders
     * @return array<string, mixed>
     */
    protected function resolvePlaceholders(array $attributes, array $placeholders): array
    {
        $resolved = [];

        foreach ($attributes as $key => $value) {
            if (is_string($value)) {
                $resolved[$key] = strtr($value, $placeholders);
            } else {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }
}
