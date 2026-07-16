<?php

namespace Moe\Auth\Http\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Moe\Auth\Actions\RegisterAction;

class Register extends Component
{
    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|string|email|max:255')]
    public string $email = '';

    #[Rule('required|string|min:8|confirmed')]
    public string $password = '';

    #[Rule('required|string')]
    public string $password_confirmation = '';

    public function register(RegisterAction $action): void
    {
        $this->validate();

        $action->execute([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        $this->redirect(
            config('moe-auth.redirects.register'),
            navigate: true,
        );
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('moe-auth::register');
    }
}
