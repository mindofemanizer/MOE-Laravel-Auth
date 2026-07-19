<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Sign in to your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or
                <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    create a new account
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

        {{-- Login Method Tabs --}}
        <div class="flex border-b border-gray-200">
            @if (in_array('password', $this->enabledMethods()))
                <button wire:click="$set('loginMethod', 'password')"
                    class="flex-1 py-2 text-center text-sm font-medium {{ $loginMethod === 'password' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Password
                </button>
            @endif
            @if (in_array('otp', $this->enabledMethods()))
                <button wire:click="$set('loginMethod', 'otp')"
                    class="flex-1 py-2 text-center text-sm font-medium {{ $loginMethod === 'otp' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                    OTP Code
                </button>
            @endif
        </div>

        <form wire:submit="login" class="mt-8 space-y-6">
            @if ($loginMethod === 'password')
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <input wire:model="email" id="email" type="email" required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="you@example.com">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input wire:model="password" id="password" type="password" required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="••••••••">
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input wire:model="remember" id="remember" type="checkbox"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
                        </div>
                        <div class="text-sm">
                            <a href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                                Forgot your password?
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($loginMethod === 'otp')
                <div class="space-y-4">
                    @if (! $otpSent)
                        <div>
                            <label for="otpIdentifier" class="block text-sm font-medium text-gray-700">Email or Phone</label>
                            <input wire:model="otpIdentifier" id="otpIdentifier" type="text" required
                                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="you@example.com or +62812xxxxxxx">
                            @error('otpIdentifier') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Channel</label>
                            <div class="flex space-x-4">
                                @if (config('moe-auth.otp.channels.email.enabled', false))
                                    <label class="flex items-center">
                                        <input wire:model="otpChannel" type="radio" value="email" class="mr-1">
                                        Email
                                    </label>
                                @endif
                                @if (config('moe-auth.otp.channels.whatsapp.enabled', false))
                                    <label class="flex items-center">
                                        <input wire:model="otpChannel" type="radio" value="whatsapp" class="mr-1">
                                        WhatsApp
                                    </label>
                                @endif
                                @if (config('moe-auth.otp.channels.sms.enabled', false))
                                    <label class="flex items-center">
                                        <input wire:model="otpChannel" type="radio" value="sms" class="mr-1">
                                        SMS
                                    </label>
                                @endif
                            </div>
                        </div>

                        <button wire:click="sendOtp" type="button"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Send OTP Code
                        </button>
                    @else
                        <div>
                            <label for="otpCode" class="block text-sm font-medium text-gray-700">Enter Code</label>
                            <input wire:model="otpCode" id="otpCode" type="text" required maxlength="{{ config('moe-auth.otp.length', 6) }}"
                                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center text-2xl tracking-widest"
                                placeholder="000000">
                            @error('otpCode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <button wire:click="sendOtp" type="button" wire:loading.attr="disabled"
                            class="text-sm text-indigo-600 hover:text-indigo-500 {{ $otpCooldown > 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $otpCooldown > 0 ? 'disabled' : '' }}>
                            Resend code {{ $otpCooldown > 0 ? "({$otpCooldown}s)" : '' }}
                        </button>
                    @endif
                </div>
            @endif

            <div>
                <button type="submit" wire:loading.attr="disabled"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Sign in
                </button>
            </div>
        </form>

        {{-- Google OAuth --}}
        @if (in_array('google', $this->enabledMethods()))
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 text-gray-500">Or continue with</span>
                    </div>
                </div>

                <div class="mt-6">
                    <button wire:click="loginWithGoogle"
                        class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Google
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    Livewire.on('otp-cooldown-started', (seconds) => {
        let remaining = seconds;
        const interval = setInterval(() => {
            remaining--;
            Livewire.find('{{ $this->getId() }}').set('otpCooldown', remaining);
            if (remaining <= 0) clearInterval(interval);
        }, 1000);
    });
</script>
@endpush
