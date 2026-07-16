<?php

namespace Moe\Auth\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function execute(string $email, string $password, bool $remember = false): void
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        session()->regenerate();
    }
}
