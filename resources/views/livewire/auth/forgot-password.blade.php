<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Reset your password
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter your email to receive a reset link.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="text-sm text-green-700">{{ session('status') }}</div>
            </div>
        @endif

        <form wire:submit.prevent="sendResetLink" class="mt-8 space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input wire:model="email" id="email" type="email" required
                    class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="you@example.com">
                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Send Reset Link
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 text-sm">
                    Back to login
                </a>
            </div>
        </form>
    </div>
</div>
