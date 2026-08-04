<?php

use Illuminate\Support\Facades\Auth;
use Moe\Auth\Actions\RegisterAction;

it('creates user and logs in', function () {
    $action = new RegisterAction();
    $user = $action->execute([
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user)->not->toBeNull();
    expect($user->name)->toBe('New User');
    expect($user->email)->toBe('new@example.com');
    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toEqual($user->id);
});

it('requires unique email', function () {
    $action = new RegisterAction();
    $action->execute([
        'name' => 'First',
        'email' => 'same@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(fn () => $action->execute([
        'name' => 'Second',
        'email' => 'same@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});
