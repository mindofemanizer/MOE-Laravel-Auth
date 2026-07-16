<?php

namespace Moe\Auth\Http\Livewire;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ForgotPassword extends Component
{
    #[Rule('required|string|email')]
    public string $email = '';

    public ?string $status = null;

    public function sendResetLink(): void
    {
        $this->validate();

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->status = __($status);
            $this->email = '';
        } else {
            $this->addError('email', __($status));
        }
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('moe-auth::forgot-password');
    }
}
