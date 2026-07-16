<div>
    <div>
        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.
    </div>

    @if ($status)
        <div>{{ $status }}</div>
    @endif

    <form wire:submit="sendResetLink">
        <div>
            <label for="email">Email</label>
            <input wire:model="email" id="email" type="email" autocomplete="email" autofocus>
            @error('email') <span>{{ $message }}</span> @enderror
        </div>

        <button type="submit">Email Password Reset Link</button>
    </form>

    <a wire:navigate href="{{ route('login') }}">Back to login</a>
</div>
