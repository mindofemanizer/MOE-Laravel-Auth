<?php

namespace Moe\Auth\Http\Livewire;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Moe\Auth\Services\OtpService;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $password_confirmation = '';

    public $otpCode = '';
    public $otpSent = false;
    public $otpVerified = false;
    public $otpChannel = 'email';
    public $otpCooldown = 0;
    public $step = 1;

    public $status;
    public $error;

    public function mount()
    {
        if (! config('moe-auth.features.registration', true)) {
            abort(403, 'Registration is disabled.');
        }
    }

    public function sendOtp()
    {
        if (config('moe-auth.features.otp', false) === false) {
            $this->otpVerified = true;
            $this->step = 2;
            return;
        }

        $identifier = $this->email;

        if (empty($identifier)) {
            $this->error = 'Please enter your email.';
            return;
        }

        $throttleKey = $this->throttleKey($identifier);

        if (RateLimiter::tooManyAttempts($throttleKey, $this->otpMaxAttempts())) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->error = $this->throttledMessage($seconds);
            return;
        }

        RateLimiter::hit($throttleKey, $this->otpDecaySeconds());

        $otpService = app(OtpService::class);
        $code = $otpService->generate($identifier, 'register');

        $sent = $otpService->send($identifier, $code, $this->otpChannel);

        if ($sent) {
            $this->otpSent = true;
            $this->status = 'Verification code has been sent to your email.';
            $this->error = null;
            $this->startOtpCooldown();
        } else {
            $this->error = 'Failed to send verification code.';
        }
    }

    public function verifyOtp()
    {
        $this->validate([
            'otpCode' => 'required|string|digits:' . config('moe-auth.otp.length', 6),
        ]);

        $otpService = app(OtpService::class);

        if (! $otpService->verify($this->email, $this->otpCode, 'register')) {
            $this->error = 'Invalid or expired verification code.';
            return;
        }

        $this->otpVerified = true;
        $this->step = 2;
        $this->status = 'Email verified! Please complete your registration.';
        $this->error = null;

        $this->afterOtpVerified();
    }

    protected function afterOtpVerified(): void
    {
    }

    public function register()
    {
        $this->validate($this->registerRules());

        $this->beforeRegister();

        $user = $this->getUserModel()::create($this->registerData());

        Auth::login($user);
        session()->regenerate();

        $this->afterRegister($user);

        return redirect($this->redirectPath());
    }

    protected function beforeRegister(): void
    {
    }

    protected function afterRegister(User $user): void
    {
    }

    protected function registerData(): array
    {
        return array_merge([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ], $this->extraRegisterData());
    }

    protected function extraRegisterData(): array
    {
        $data = [];

        if ($this->phone) {
            $data['phone'] = $this->phone;
        }

        if ($this->otpVerified) {
            $data['email_verified_at'] = now();
        }

        return $data;
    }

    // ─── Configurable Methods (override in child) ───

    protected function getUserModel(): string
    {
        return config('moe-auth.user_model', config('auth.providers.users.model'));
    }

    protected function redirectPath(): string
    {
        return config('moe-auth.redirects.register', '/dashboard');
    }

    protected function registerRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:' . app($this->getUserModel())->getTable() . ',email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    protected function otpMaxAttempts(): int
    {
        return config('moe-auth.rate_limit.otp_send.max_attempts', 3);
    }

    protected function otpDecaySeconds(): int
    {
        return config('moe-auth.rate_limit.otp_send.decay_minutes', 5) * 60;
    }

    protected function throttleKey(string $identifier): string
    {
        return 'moe-auth:register-otp:' . $identifier;
    }

    protected function throttledMessage(int $seconds): string
    {
        return "Too many attempts. Please try again in {$seconds} seconds.";
    }

    protected function startOtpCooldown(): void
    {
        $this->otpCooldown = config('moe-auth.otp.throttle', 60);
        $this->dispatch('otp-cooldown-started', $this->otpCooldown);
    }

    public function render()
    {
        return view('moe-auth::livewire.auth.register');
    }
}
