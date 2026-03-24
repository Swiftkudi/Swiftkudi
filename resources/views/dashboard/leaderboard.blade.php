@extends('layouts.app')

@section('title', 'Leaderboard - SwiftKudi')

@section('content')
<div class="py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Leaderboard</h1>
            <p class="text-gray-600 mt-1">See the top performers on SwiftKudi</p>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8">
                    <a href="{{ route('dashboard.leaderboard', ['type' => 'earners']) }}" class="py-4 px-1 border-b-2 font-medium text-sm {{ $type === 'earners' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-naira-sign mr-2"></i> Top Earners
                    </a>
                    <a href="{{ route('dashboard.leaderboard', ['type' => 'tasks']) }}" class="py-4 px-1 border-b-2 font-medium text-sm {{ $type === 'tasks' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-tasks mr-2"></i> Most Tasks
                    </a>
                    <a href="{{ route('dashboard.leaderboard', ['type' => 'streaks']) }}" class="py-4 px-1 border-b-2 font-medium text-sm {{ $type === 'streaks' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-fire mr-2"></i> Top Streaks
                    </a>
                    <a href="{{ route('dashboard.leaderboard', ['type' => 'xp']) }}" class="py-4 px-1 border-b-2 font-medium text-sm {{ $type === 'xp' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-star mr-2"></i> Highest XP
                    </a>
                </nav>
            </div>
        </div>

        <!-- Your Rank -->
        @if($currentUserRank)
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-trophy text-indigo-500 mr-3"></i>
                    <div>
                        <p class="font-medium text-indigo-800">Your Rank</p>
                        <p class="text-sm text-indigo-600">You're ranked #{{ $currentUserRank }} among all users</p>
                    </div>
                </div>
                <span class="text-2xl font-bold text-indigo-600">#{{ $currentUserRank }}</span>
            </div>
        </div>
        @endif

        <!-- Leaderboard -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                @switch($type)
                                    @case('earners') Earnings @break
                                    @case('tasks') Tasks Completed @break
                                    @case('streaks') Day Streak @break
                                    @case('xp') Experience @break
                                @endswitch
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($leaders as $index => $leader)
                            <tr class="{{ $leader->user_id === Auth::id() ? 'bg-indigo-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($index < 3)
                                        <span class="flex items-center justify-center w-8 h-8 rounded-full
                                            @if($index === 0) bg-yellow-400 text-yellow-900 @endif
                                            @if($index === 1) bg-gray-300 text-gray-700 @endif
                                            @if($index === 2) bg-orange-400 text-orange-900 @endif
                                            font-bold text-sm">
                                            {{ $index + 1 }}
                                        </span>
                                    @else
                                        <span class="flex items-center justify-center w-8 h-8 text-gray-500 font-medium text-sm">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                            <span class="text-indigo-600 font-medium">
                                                {{ substr($leader->user->name ?? 'U', 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $leader->user->name ?? 'Unknown' }}
                                                @if($leader->user_id === Auth::id())
                                                    <span class="ml-2 text-xs text-indigo-600">(You)</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $leader->user->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        Level {{ $leader->user->level ?? 1 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @switch($type)
                                        @case('earners')
                                            <span class="text-green-600 font-bold">₦{{ number_format($leader->total_earned ?? 0, 2) }}</span>
                                            @break
                                        @case('tasks')
                                            <span class="text-blue-600 font-bold">{{ $leader->task_completions_count ?? 0 }}</span>
                                            @break
                                        @case('streaks')
                                            <span class="text-orange-600 font-bold">
                                                {{ $leader->daily_streak ?? 0 }}
                                                <i class="fas fa-fire ml-1 text-orange-500"></i>
                                            </span>
                                            @break
                                        @case('xp')
                                            <span class="text-purple-600 font-bold">{{ number_format($leader->experience_points ?? 0, 0) }} XP</span>
                                            @break
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($leaders->isEmpty())
                <div class="p-8 text-center">
                    <i class="fas fa-trophy text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500">No data available yet</p>
                    <p class="text-sm text-gray-400 mt-2">Complete tasks to appear on the leaderboard!</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
