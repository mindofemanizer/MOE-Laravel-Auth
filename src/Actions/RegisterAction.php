<?php

namespace Moe\Auth\Actions;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterAction
{
    /**
     * Create a user/client and optionally log them in on the given guard.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $extra
     */
    public function execute(
        array $data,
        array $extra = [],
        ?string $guard = null,
        ?string $model = null,
        bool $login = true,
    ): Authenticatable {
        $modelClass = $model
            ?? config('moe-auth.user_model')
            ?? config('auth.providers.users.model');

        $modelTable = (new $modelClass)->getTable();
        $passwordRules = [
            'required',
            'string',
            Password::min((int) config('moe-auth.password.min', 8)),
        ];

        if (array_key_exists('password_confirmation', $data)) {
            $passwordRules[] = 'confirmed';
        }

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:'.$modelTable.',email'],
            'password' => $passwordRules,
        ])->validate();

        // Pass plain password — Laravel's `hashed` cast (User/Client) will hash once.
        // Fallback Hash::make only when the model does not cast password as hashed.
        $password = $validated['password'];
        if (! $this->modelHashesPassword($modelClass)) {
            $password = Hash::make($password);
        }

        $userData = array_merge($extra, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $password,
        ]);

        /** @var Authenticatable $user */
        $user = $modelClass::create($userData);

        event(new Registered($user));

        if ($login) {
            $guardName = $guard
                ?? config('moe-auth.guard')
                ?? config('auth.defaults.guard', 'web');

            Auth::guard($guardName)->login($user);
            session()->regenerate();
        }

        return $user;
    }

    /**
     * Detect whether the model will hash passwords via cast/mutator.
     */
    protected function modelHashesPassword(string $modelClass): bool
    {
        if (! class_exists($modelClass)) {
            return false;
        }

        try {
            $model = new $modelClass;
            $casts = method_exists($model, 'getCasts') ? $model->getCasts() : [];

            return ($casts['password'] ?? null) === 'hashed';
        } catch (\Throwable) {
            return false;
        }
    }
}
