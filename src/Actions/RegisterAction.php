<?php

namespace Moe\Auth\Actions;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterAction
{
    public function execute(array $data): mixed
    {
        $model = config('auth.providers.users.model');

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . (new $model)->getTable()],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validated = $validator->validate();

        $user = $model::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return $user;
    }
}
