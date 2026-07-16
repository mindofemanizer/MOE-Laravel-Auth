<?php

namespace Moe\Auth\Http\Livewire;

use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Moe\Auth\Services\GoogleService;
use Moe\Auth\Services\OtpService;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    // OTP
    public $otpIdentifier = '';
    public $otpCode = '';
    public $loginMethod = 'password'; // password, otp
    public $otpChannel = 'email';
    public $otpSent = false;
    public $otpCooldown = 0;

    // General
    public $status;
    public $error;
    public $throttled = false;
    public $throttledSeconds = 0;

    protected $listeners = [
        'updateIdentifier' => 'updateIdentifier',
        'startOtpCooldown' => 'startOtpCooldown',
    ];

    public function updateIdentifier($value)
    {
        $this->otpIdentifier = $value;
    }

    public function mount()
    {
        if (session('status')) {
            $this->status = session('status');
        }

        $this->otpIdentifier = request()->input('email', '');
    }

    public function updatedEmail()
    {
        if ($this->loginMethod === 'otp') {
            $this->otpIdentifier = $this->email;
        }
    }

    public function updatedLoginMethod($value)
    {
        if ($this->loginMethod === 'otp') {
            $this->otpIdentifier = $this->email;
        }
    }

    public function sendOtp()
    {
        if (config('moe-auth.features.otp', false) === false) {
            $this->error = 'OTP login is not enabled.';
            return;
        }

        $identifier = $this->otpIdentifier ?: $this->email;

        if (empty($identifier)) {
            $this->error = 'Please enter your email or phone number.';
            return;
        }

        // Rate limit
        $throttleKey = 'otp-send:' . $identifier;
        $maxAttempts = config('moe-auth.rate_limit.otp_send.max_attempts', 3);
        $decayMinutes = config('moe-auth.rate_limit.otp_send.decay_minutes', 5);

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $this->throttledSeconds = RateLimiter::availableIn($throttleKey);
            $this->error = "Too many attempts. Please try again in {$this->throttledSeconds} seconds.";
            return;
        }

        RateLimiter::hit($throttleKey, $decayMinutes * 60);

        $otpService = app(OtpService::class);
        $code = $otpService->generate($identifier, 'login');

        $sent = $otpService->send($identifier, $code, $this->otpChannel);

        if ($sent) {
            $this->otpSent = true;
            $this->status = 'OTP code has been sent to your ' . $this->otpChannel . '.';
            $this->error = null;
            $this->startOtpCooldown();
        } else {
            $this->error = 'Failed to send OTP. Please try again.';
        }
    }

    public function startOtpCooldown()
    {
        $this->otpCooldown = config('moe-auth.otp.throttle', 60);
        $this->dispatch('otp-cooldown-started', $this->otpCooldown);
    }

    public function login()
    {
        if ($this->loginMethod === 'otp') {
            return $this->loginWithOtp();
        }

        return $this->loginWithPassword();
    }

    protected function loginWithPassword()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $throttleKey = 'login:' . $this->email;
        $maxAttempts = config('moe-auth.rate_limit.login.max_attempts', 5);
        $decayMinutes = config('moe-auth.rate_limit.login.decay_minutes', 1);

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $this->throttledSeconds = RateLimiter::availableIn($throttleKey);
            $this->error = "Too many login attempts. Please try again in {$this->throttledSeconds} seconds.";
            return;
        }

        if (! auth()->attempt($this->only('email', 'password'), $this->remember)) {
            RateLimiter::hit($throttleKey, $decayMinutes * 60);
            $this->error = 'Invalid email or password.';
            return;
        }

        RateLimiter::clear($throttleKey);

        session()->regenerate();

        return redirect()->intended(config('moe-auth.redirects.login', '/dashboard'));
    }

    protected function loginWithOtp()
    {
        $this->validate([
            'otpIdentifier' => 'required',
            'otpCode' => 'required|string|digits:' . config('moe-auth.otp.length', 6),
        ]);

        $otpService = app(OtpService::class);

        if (! $otpService->verify($this->otpIdentifier, $this->otpCode, 'login')) {
            $this->error = 'Invalid or expired OTP code.';
            return;
        }

        // Find or create user by email/phone
        $userModel = config('moe-auth.user_model', config('auth.providers.users.model'));
        $user = $userModel::where('email', $this->otpIdentifier)
            ->orWhere('phone', $this->otpIdentifier)
            ->first();

        if (! $user) {
            $this->error = 'No account found with this identifier.';
            return;
        }

        auth()->login($user, remember: true);
        session()->regenerate();

        return redirect()->intended(config('moe-auth.redirects.login', '/dashboard'));
    }

    public function loginWithGoogle()
    {
        if (config('moe-auth.features.google_oauth', false) === false) {
            $this->error = 'Google login is not enabled.';
            return;
        }

        return app(GoogleService::class)->redirect();
    }

    public function render()
    {
        return view('moe-auth::livewire.auth.login');
    }
}
