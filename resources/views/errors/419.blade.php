@component('layouts.guest')

<div class="bg-dark-900 rounded-lg shadow-lg p-6 text-center">
    <h1 class="text-2xl font-bold mb-2">Session Expired</h1>
    <p class="text-gray-400 mb-4">For your security, your session has expired. Please sign in again to continue.</p>

    @if(session('warning'))
        <div class="mb-4 text-sm text-yellow-300">{{ session('warning') }}</div>
    @endif

    <div class="flex items-center justify-center gap-3">
        <a href="{{ route('login') }}" class="inline-block px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-500">Sign in</a>
        <a href="{{ route('home') }}" class="inline-block px-4 py-2 rounded border border-gray-700 text-gray-200">Return to homepage</a>
    </div>

</div>

@endcomponent
