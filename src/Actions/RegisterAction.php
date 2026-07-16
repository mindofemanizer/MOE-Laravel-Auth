<?php

namespace Moe\Auth\Actions;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterAction
{
    public function execute(array $data, array $extra = []): User
    {
        $model = config('moe-auth.user_model', config('auth.providers.users.model'));

        $userData = array_merge([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ], $extra);

        /** @var User $user */
        $user = $model::create($userData);

        event(new Registered($user));

        Auth::login($user);

        return $user;
    }
}
