<?php

namespace Moe\Auth\Http\Livewire;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Moe\Auth\Services\GoogleService;
use Moe\Auth\Services\OtpService;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    public $otpIdentifier = '';
    public $otpCode = '';
    public $loginMethod = 'password';
    public $otpChannel = 'email';
    public $otpSent = false;
    public $otpCooldown = 0;

    public $status;
    public $error;
    public $throttled = false;
    public $throttledSeconds = 0;

    public function mount()
    {
        if (session('status')) {
            $this->status = session('status');
        }
        $this->otpIdentifier = request()->input('email', '');
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
        $this->validate($this->loginRules());

        $throttleKey = $this->throttleKey('login', $this->email);

        if (RateLimiter::tooManyAttempts($throttleKey, $this->loginMaxAttempts())) {
            $this->throttledSeconds = RateLimiter::availableIn($throttleKey);
            $this->error = $this->throttledMessage($this->throttledSeconds);
            return;
        }

        $user = $this->authenticate($this->email, $this->password);

        if (! $user) {
            RateLimiter::hit($throttleKey, $this->loginDecaySeconds());
            $this->error = $this->loginFailedMessage();
            return;
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, $this->remember);
        session()->regenerate();

        $this->afterLogin($user);

        return redirect()->intended($this->redirectPath());
    }

    protected function authenticate(string $email, string $password): ?User
    {
        $conditions = array_merge(
            ['email' => $email, 'password' => $password],
            $this->extraLoginConditions(),
        );

        if (! Auth::validate($conditions)) {
            return null;
        }

        return $this->getUserModel()::where('email', $email)->first();
    }

    protected function extraLoginConditions(): array
    {
        return [];
    }

    protected function afterLogin(User $user): void
    {
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

        $throttleKey = $this->throttleKey('otp-send', $identifier);

        if (RateLimiter::tooManyAttempts($throttleKey, $this->otpSendMaxAttempts())) {
            $this->throttledSeconds = RateLimiter::availableIn($throttleKey);
            $this->error = $this->throttledMessage($this->throttledSeconds);
            return;
        }

        RateLimiter::hit($throttleKey, $this->otpSendDecaySeconds());

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

        $userModel = $this->getUserModel();
        $user = $userModel::where('email', $this->otpIdentifier)
            ->orWhere('phone', $this->otpIdentifier)
            ->first();

        if (! $user) {
            $this->error = 'No account found with this identifier.';
            return;
        }

        Auth::login($user, remember: true);
        session()->regenerate();

        $this->afterLogin($user);

        return redirect()->intended($this->redirectPath());
    }

    public function loginWithGoogle()
    {
        if (config('moe-auth.features.google_oauth', false) === false) {
            $this->error = 'Google login is not enabled.';
            return;
        }

        return app(GoogleService::class)->redirect();
    }

    // ─── Configurable Methods (override in child) ───

    protected function getUserModel(): string
    {
        return config('moe-auth.user_model', config('auth.providers.users.model'));
    }

    protected function redirectPath(): string
    {
        return config('moe-auth.redirects.login', '/dashboard');
    }

    protected function loginRules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    protected function loginMaxAttempts(): int
    {
        return config('moe-auth.rate_limit.login.max_attempts', 5);
    }

    protected function loginDecaySeconds(): int
    {
        return config('moe-auth.rate_limit.login.decay_minutes', 1) * 60;
    }

    protected function otpSendMaxAttempts(): int
    {
        return config('moe-auth.rate_limit.otp_send.max_attempts', 3);
    }

    protected function otpSendDecaySeconds(): int
    {
        return config('moe-auth.rate_limit.otp_send.decay_minutes', 5) * 60;
    }

    protected function throttleKey(string $type, string $identifier): string
    {
        return "moe-auth:{$type}:{$identifier}";
    }

    protected function throttledMessage(int $seconds): string
    {
        return "Too many attempts. Please try again in {$seconds} seconds.";
    }

    protected function loginFailedMessage(): string
    {
        return 'Invalid email or password.';
    }

    protected function startOtpCooldown(): void
    {
        $this->otpCooldown = config('moe-auth.otp.throttle', 60);
        $this->dispatch('otp-cooldown-started', $this->otpCooldown);
    }

    public function render()
    {
        return view('moe-auth::livewire.auth.login');
    }
}
