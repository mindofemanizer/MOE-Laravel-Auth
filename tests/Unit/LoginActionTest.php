<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Moe\Auth\Actions\LoginAction;
use Moe\Auth\Tests\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);
});

it('logs in with valid credentials', function () {
    $action = new LoginAction();
    $action->execute('test@example.com', 'password');

    $this->assertAuthenticatedAs($this->user);
});

it('rejects invalid password', function () {
    expect(fn () => (new LoginAction())->execute('test@example.com', 'wrong-password'))
        ->toThrow(ValidationException::class);
});

it('rejects nonexistent email', function () {
    expect(fn () => (new LoginAction())->execute('nonexistent@example.com', 'password'))
        ->toThrow(ValidationException::class);
});
