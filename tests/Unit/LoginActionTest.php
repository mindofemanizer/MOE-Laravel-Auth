<?php

namespace Moe\Auth\Tests\Unit;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Moe\Auth\Actions\LoginAction;
use Moe\Auth\Tests\TestCase;
use Moe\Auth\Tests\User;

class LoginActionTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_login_with_valid_credentials(): void
    {
        $action = new LoginAction();
        $action->execute('test@example.com', 'password');

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_with_invalid_password(): void
    {
        $this->expectException(ValidationException::class);

        $action = new LoginAction();
        $action->execute('test@example.com', 'wrong-password');
    }

    public function test_login_with_nonexistent_email(): void
    {
        $this->expectException(ValidationException::class);

        $action = new LoginAction();
        $action->execute('nonexistent@example.com', 'password');
    }
}
