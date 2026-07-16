<?php

namespace Moe\Auth\Http\Controllers;

use Illuminate\Http\RedirectResponse;
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
            session()->flash('success', 'Welcome back, ' . $user->name . '!');
            return redirect(config('moe-auth.redirects.login', '/dashboard'));
        }

        return redirect()->route('login')->with('error', 'Google authentication failed.');
    }
}
