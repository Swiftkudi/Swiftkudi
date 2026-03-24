@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="min-h-screen pt-8 pb-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Notifications</h1>
                @if($unreadCount > 0)
                    <p class="text-sm text-gray-400 mt-1">{{ $unreadCount }} unread notification{{ $unreadCount === 1 ? '' : 's' }}</p>
                @else
                    <p class="text-sm text-gray-400 mt-1">All caught up!</p>
                @endif
            </div>
            <a href="{{ route('dashboard') }}"
               class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>

        <!-- Notifications List -->
        <div class="bg-dark-900 border border-dark-700 rounded-2xl shadow-xl overflow-hidden">
            @forelse($notifications as $notification)
                <div class="flex items-start gap-4 px-5 py-4 border-b border-dark-700 last:border-b-0
                    {{ $notification->is_read ? '' : 'bg-indigo-500/5 border-l-4 border-l-indigo-500' }}">

                    {{-- Icon --}}
                    @php
                        $iconMap = [
                            'task_approved' => ['fa-check-circle', 'text-green-400',  'bg-green-500/10'],
                            'task_rejected' => ['fa-times-circle', 'text-red-400',    'bg-red-500/10'],
                            'new_task'      => ['fa-tasks',        'text-indigo-400', 'bg-indigo-500/10'],
                            'earnings'      => ['fa-coins',        'text-yellow-400', 'bg-yellow-500/10'],
                            'withdrawal'    => ['fa-wallet',       'text-blue-400',   'bg-blue-500/10'],
                            'level_up'      => ['fa-arrow-up',     'text-purple-400', 'bg-purple-500/10'],
                            'badge_earned'  => ['fa-medal',        'text-yellow-400', 'bg-yellow-500/10'],
                            'referral'      => ['fa-users',        'text-green-400',  'bg-green-500/10'],
                            'system'        => ['fa-bell',         'text-gray-400',   'bg-gray-500/10'],
                        ];
                        [$ico, $icoColor, $icoBg] = $iconMap[$notification->type] ?? $iconMap['system'];
                    @endphp
                    <div class="flex-shrink-0 mt-0.5 w-10 h-10 rounded-xl {{ $icoBg }} flex items-center justify-center">
                        <i class="fas {{ $ico }} {{ $icoColor }}"></i>
                    </div>

                    {{-- Body --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white">{{ $notification->title }}</p>
                        <p class="text-sm text-gray-400 mt-0.5">{{ $notification->message }}</p>
                        <p class="text-xs text-gray-600 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                            &middot;
                            <span class="capitalize">{{ str_replace('_', ' ', $notification->type) }}</span>
                        </p>
                    </div>

                    {{-- Unread dot --}}
                    @unless($notification->is_read)
                        <div class="flex-shrink-0 mt-2 w-2.5 h-2.5 rounded-full bg-indigo-500"></div>
                    @endunless
                </div>
            @empty
                <div class="py-16 text-center">
                    <i class="fas fa-bell-slash text-4xl text-gray-600 mb-3 block"></i>
                    <p class="text-gray-400">You have no notifications yet.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
