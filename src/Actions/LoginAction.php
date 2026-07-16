<?php

namespace Moe\Auth\Actions;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function execute(string $email, string $password, bool $remember = false, array $extraConditions = []): ?User
    {
        $conditions = array_merge(
            ['email' => $email, 'password' => $password],
            $extraConditions,
        );

        if (! Auth::attempt($conditions, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        session()->regenerate();

        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }

    public function validateOnly(string $email, array $extraConditions = []): bool
    {
        $conditions = array_merge(
            ['email' => $email],
            $extraConditions,
        );

        return Auth::validate($conditions);
    }
}
