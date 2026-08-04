<?php

namespace Moe\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    /**
     * Attempt login against the given (or configured) guard.
     *
     * @param  array<string, mixed>  $extraConditions
     */
    public function execute(
        string $email,
        string $password,
        bool $remember = false,
        array $extraConditions = [],
        ?string $guard = null,
    ): ?Authenticatable {
        $guardName = $this->resolveGuard($guard);

        $conditions = array_merge(
            ['email' => $email, 'password' => $password],
            $extraConditions,
        );

        if (! Auth::guard($guardName)->attempt($conditions, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        session()->regenerate();

        return Auth::guard($guardName)->user();
    }

    /**
     * @param  array<string, mixed>  $extraConditions
     */
    public function validateOnly(string $email, array $extraConditions = [], ?string $guard = null): bool
    {
        $guardName = $this->resolveGuard($guard);

        $conditions = array_merge(
            ['email' => $email],
            $extraConditions,
        );

        return Auth::guard($guardName)->validate($conditions);
    }

    protected function resolveGuard(?string $guard): string
    {
        return $guard
            ?? config('moe-auth.guard')
            ?? config('auth.defaults.guard', 'web');
    }
}
