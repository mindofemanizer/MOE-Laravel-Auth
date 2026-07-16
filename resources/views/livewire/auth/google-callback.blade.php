<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center">
        @if ($error)
            <div class="rounded-md bg-red-50 p-4 mb-4">
                <div class="text-sm text-red-700">{{ $error }}</div>
            </div>
            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                Back to login
            </a>
        @else
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="mt-4 text-gray-600">Authenticating with Google...</p>
        @endif
    </div>
</div>
