<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    sign in to existing account
                </a>
            </p>
        </div>

        @if ($status)
            <div class="rounded-md bg-green-50 p-4">
                <div class="text-sm text-green-700">{{ $status }}</div>
            </div>
        @endif

        @if ($error)
            <div class="rounded-md bg-red-50 p-4">
                <div class="text-sm text-red-700">{{ $error }}</div>
            </div>
        @endif

        {{-- Step Indicator --}}
        <div class="flex items-center justify-center space-x-4">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full {{ $step >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600' }} flex items-center justify-center text-sm font-medium">1</div>
                <span class="ml-2 text-sm font-medium text-gray-700">Verify</span>
            </div>
            <div class="w-8 h-0.5 bg-gray-300"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full {{ $step >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600' }} flex items-center justify-center text-sm font-medium">2</div>
                <span class="ml-2 text-sm font-medium text-gray-700">Details</span>
            </div>
        </div>

        <form wire:submit="{{ $step === 1 ? 'verifyOtp' : 'register' }}" class="mt-8 space-y-6">
            {{-- Step 1: Email + OTP --}}
            @if ($step === 1)
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <input wire:model="email" id="email" type="email" required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="you@example.com">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    @if (! $otpSent)
                        <button wire:click="sendOtp" type="button"
                            class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Send Verification Code
                        </button>
                    @else
                        <div>
                            <label for="otpCode" class="block text-sm font-medium text-gray-700">Enter Code</label>
                            <input wire:model="otpCode" id="otpCode" type="text" required maxlength="{{ config('moe-auth.otp.length', 6) }}"
                                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center text-2xl tracking-widest"
                                placeholder="000000">
                            @error('otpCode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <button wire:click="sendOtp" type="button" wire:loading.attr="disabled"
                                class="text-sm text-indigo-600 hover:text-indigo-500 {{ $otpCooldown > 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $otpCooldown > 0 ? 'disabled' : '' }}>
                                Resend code {{ $otpCooldown > 0 ? "({$otpCooldown}s)" : '' }}
                            </button>
                        </div>

                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Verify Code
                        </button>
                    @endif
                </div>
            @endif

            {{-- Step 2: Registration Form --}}
            @if ($step === 2)
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input wire:model="name" id="name" type="text" required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="John Doe">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone (optional)</label>
                        <input wire:model="phone" id="phone" type="tel"
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="+62812xxxxxxx">
                        @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="reg_password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input wire:model="password" id="reg_password" type="password" required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Min 8 characters">
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input wire:model="password_confirmation" id="password_confirmation" type="password" required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="••••••••">
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Account
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>

@push('scripts')
<script>
    Livewire.on('otp-cooldown-started', (seconds) => {
        let remaining = seconds;
        const interval = setInterval(() => {
            remaining--;
            Livewire.find('{{ $this->id }}').set('otpCooldown', remaining);
            if (remaining <= 0) clearInterval(interval);
        }, 1000);
    });
</script>
@endpush
