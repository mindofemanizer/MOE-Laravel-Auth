<?php

namespace Moe\Auth\Http\Livewire;

use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Moe\Auth\Actions\LoginAction;

class Login extends Component
{
    #[Rule('required|string|email')]
    public string $email = '';

    #[Rule('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(LoginAction $action): void
    {
        $this->validate();

        $action->execute($this->email, $this->password, $this->remember);

        $this->redirect(
            Session::pull('url.intended', config('moe-auth.redirects.login')),
            navigate: true,
        );
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('moe-auth::login');
    }
}
