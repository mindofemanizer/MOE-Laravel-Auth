<div>
    <form wire:submit="resetPassword">
        <div>
            <label for="email">Email</label>
            <input wire:model="email" id="email" type="email" autocomplete="email" autofocus>
            @error('email') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password">Password</label>
            <input wire:model="password" id="password" type="password" autocomplete="new-password">
            @error('password') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password_confirmation">Confirm Password</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" autocomplete="new-password">
        </div>

        <button type="submit">Reset Password</button>
    </form>
</div>
