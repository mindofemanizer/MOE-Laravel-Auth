<?php

namespace Moe\Auth\Http\Livewire;

use Livewire\Component;
use Moe\Auth\Services\GoogleService;

class GoogleCallback extends Component
{
    public $error;

    public function mount()
    {
        try {
            $googleService = app(GoogleService::class);
            $user = $googleService->handleCallback();

            if ($user) {
                session()->flash('success', 'Welcome back, ' . $user->name . '!');
                return redirect(config('moe-auth.redirects.login', '/dashboard'));
            }

            $this->error = 'Could not authenticate with Google.';
        } catch (\Exception $e) {
            $this->error = 'Google authentication failed: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('moe-auth::livewire.auth.google-callback');
    }
}
