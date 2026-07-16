<?php

namespace Moe\Auth\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
            Auth::login($user, remember: true);
            session()->regenerate();

            return redirect(config('moe-auth.redirects.login', '/dashboard'));
        }

        return redirect()->route('login')->with('error', 'Google authentication failed.');
    }
}
