<?php

namespace Moe\Auth\Actions;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

        $this->validate($data, $modelClass);

        // Pass plain password — Laravel's `hashed` cast (User/Client) will hash once.
        // Fallback Hash::make only when the model does not cast password as hashed.
        $password = $data['password'];
        if (! $this->modelHashesPassword($modelClass)) {
            $password = Hash::make($password);
        }

        $userData = array_merge([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $password,
        ], $extra);

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
     * Validate registration data.
     *
     * @param  array<string, mixed>  $data
     * @param  class-string  $modelClass
     *
     * @throws ValidationException
     */
    protected function validate(array $data, string $modelClass): void
    {
        $table = (new $modelClass)->getTable();

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:' . $table . ',email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
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
