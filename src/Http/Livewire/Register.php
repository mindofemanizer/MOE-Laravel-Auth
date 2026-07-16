<?php

namespace Moe\Auth\Http\Livewire;

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

    // OTP
    public $otpCode = '';
    public $otpSent = false;
    public $otpVerified = false;
    public $otpChannel = 'email';
    public $otpCooldown = 0;
    public $step = 1; // 1: form, 2: otp verification

    // General
    public $status;
    public $error;

    protected $listeners = [
        'startOtpCooldown' => 'startOtpCooldown',
    ];

    public function mount()
    {
        if (! config('moe-auth.features.registration', true)) {
            abort(403, 'Registration is disabled.');
        }
    }

    public function sendOtp()
    {
        if (config('moe-auth.features.otp', false) === false) {
            $this->otpVerified = true; // Skip OTP if disabled
            return;
        }

        $identifier = $this->email;

        if (empty($identifier)) {
            $this->error = 'Please enter your email.';
            return;
        }

        // Rate limit
        $throttleKey = 'otp-register:' . $identifier;
        $maxAttempts = config('moe-auth.rate_limit.otp_send.max_attempts', 3);
        $decayMinutes = config('moe-auth.rate_limit.otp_send.decay_minutes', 5);

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->error = "Too many attempts. Please try again in {$seconds} seconds.";
            return;
        }

        RateLimiter::hit($throttleKey, $decayMinutes * 60);

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

    public function startOtpCooldown()
    {
        $this->otpCooldown = config('moe-auth.otp.throttle', 60);
        $this->dispatch('otp-cooldown-started', $this->otpCooldown);
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
    }

    public function register()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $userModel = config('moe-auth.user_model', config('auth.providers.users.model'));

        $user = $userModel::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'password' => bcrypt($this->password),
            'email_verified_at' => $this->otpVerified ? now() : null,
        ]);

        auth()->login($user);
        session()->regenerate();

        return redirect(config('moe-auth.redirects.register', '/dashboard'));
    }

    public function render()
    {
        return view('moe-auth::livewire.auth.register');
    }
}
