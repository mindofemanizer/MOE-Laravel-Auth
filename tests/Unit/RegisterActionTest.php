<?php

namespace Moe\Auth\Tests\Unit;

use Illuminate\Support\Facades\Auth;
use Moe\Auth\Actions\RegisterAction;
use Moe\Auth\Tests\TestCase;

class RegisterActionTest extends TestCase
{
    public function test_register_creates_user_and_logs_in(): void
    {
        $action = new RegisterAction();
        $user = $action->execute([
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertNotNull($user);
        $this->assertSame('New User', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_register_requires_unique_email(): void
    {
        $action = new RegisterAction();
        $action->execute([
            'name' => 'First',
            'email' => 'same@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $action->execute([
            'name' => 'Second',
            'email' => 'same@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
    }
}
