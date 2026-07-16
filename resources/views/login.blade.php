<div>
    <form wire:submit="login">
        <div>
            <label for="email">Email</label>
            <input wire:model="email" id="email" type="email" autocomplete="email" autofocus>
            @error('email') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password">Password</label>
            <input wire:model="password" id="password" type="password" autocomplete="current-password">
            @error('password') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label>
                <input wire:model="remember" type="checkbox">
                <span>Remember me</span>
            </label>
        </div>

        <button type="submit">Log in</button>

        @if (config('moe-auth.features.password_reset'))
            <a wire:navigate href="{{ route('password.request') }}">Forgot your password?</a>
        @endif
    </form>
</div>
