<?php

namespace Moe\Auth\Http\Livewire;

use Illuminate\Support\Facades\Password;
use Livewire\Component;

class ForgotPassword extends Component
{
    public string $email = '';
    public ?string $status = null;

    public function sendResetLink(): void
    {
        $this->validate($this->rules());

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->status = __($status);
            $this->email = '';
        } else {
            $this->addError('email', __($status));
        }
    }

    protected function rules(): array
    {
        return [
            'email' => 'required|string|email',
        ];
    }

    public function render()
    {
        return view('moe-auth::livewire.auth.forgot-password');
    }
}
