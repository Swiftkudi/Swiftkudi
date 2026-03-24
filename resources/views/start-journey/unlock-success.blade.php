@extends('layouts.app')

@section('title', 'Earnings Unlocked! - SwiftKudi')

@push('styles')
<style>
    .celebration {
        animation: celebration 0.5s ease-out;
    }
    @keyframes celebration {
        0% { transform: scale(0); opacity: 0; }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); opacity: 1; }
    }
    .card-glow {
        box-shadow: 0 0 60px rgba(34, 197, 94, 0.3);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-dark-900 via-dark-800 to-dark-900 flex items-center justify-center">
    <!-- Background decorations -->
    <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-green-500/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-primary-500/10 rounded-full blur-3xl"></div>

    <div class="relative max-w-lg mx-auto px-4 py-12 text-center">
        <div class="bg-dark-800/80 backdrop-blur-xl border border-green-500/30 rounded-3xl p-10 card-glow celebration">
            <!-- Trophy icon -->
            <div class="w-32 h-32 mx-auto bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-full flex items-center justify-center mb-6">
                <i class="fas fa-trophy text-6xl text-green-400"></i>
            </div>

            <!-- Celebrating emoji rain -->
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
                @for($i = 0; $i < 12; $i++)
                <div class="absolute text-2xl animate-bounce"
                     style="left: {{ rand(10, 90) }}%; top: -20px; animation-delay: {{ rand(0, 2) }}s; animation-duration: {{ rand(2, 4) }}s;">
                    {{ ['🎉', '🎊', '✨', '💰', '🚀', '🌟'][rand(0, 5)] }}
                </div>
                @endfor
            </div>

            <h1 class="text-4xl font-bold text-white mb-4">
                🎉 Earnings Unlocked!
            </h1>

            <p class="text-xl text-dark-300 mb-8">
                Congratulations! Your first campaign has been created and your earning access is now unlocked.
            </p>

            <div class="bg-dark-700/50 rounded-xl p-6 mb-8">
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-green-400">
                            <i class="fas fa-check-circle mr-2"></i>
                        </div>
                        <p class="text-dark-400 text-sm mt-1">Campaign Active</p>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-primary-400">
                            <i class="fas fa-unlock mr-2"></i>
                        </div>
                        <p class="text-dark-400 text-sm mt-1">Earnings Unlocked</p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <a href="{{ route('tasks.index') }}"
                   class="block w-full bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-bold text-lg px-8 py-4 rounded-xl transition-all transform hover:scale-105 shadow-lg shadow-green-500/25 flex items-center justify-center gap-2">
                    <i class="fas fa-rocket"></i>
                    Start Earning Now
                </a>

                <a href="{{ route('dashboard') }}"
                   class="block w-full bg-dark-700 hover:bg-dark-600 text-white font-medium px-8 py-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-home"></i>
                    Go to Dashboard
                </a>
            </div>

            <p class="mt-6 text-dark-500 text-sm">
                We've sent a confirmation email about your unlocked earning access.
            </p>
        </div>
    </div>
</div>
@endsection
