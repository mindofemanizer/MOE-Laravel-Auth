<div>
    <form wire:submit="register">
        <div>
            <label for="name">Name</label>
            <input wire:model="name" id="name" type="text" autocomplete="name" autofocus>
            @error('name') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="email">Email</label>
            <input wire:model="email" id="email" type="email" autocomplete="email">
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

        <button type="submit">Register</button>

        <a wire:navigate href="{{ route('login') }}">Already registered?</a>
    </form>
</div>
