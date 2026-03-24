@extends('layouts.app')

@section('title', 'Worker Dashboard - SwiftKudi')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Worker Dashboard</h1>
            <p class="text-gray-600 mt-1">Complete tasks and earn money</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <!-- Completed Tasks -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Completed</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['completed'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Rejected -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Rejected</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['rejected'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Earned -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-naira-sign text-indigo-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Earned</p>
                        <p class="text-2xl font-bold text-gray-900">₦{{ number_format($stats['total_earned'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Available Tasks -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-900">Available Tasks</h2>
                        <a href="{{ route('tasks.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                            View All <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <!-- Permanent Referral Bonus Task -->
                    @if($referralTask)
                    <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-users text-green-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-green-900">{{ $referralTask->title }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-star mr-1"></i>Permanent
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-green-700">{{ $referralTask->description }}</p>
                                <div class="mt-3 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <span class="text-2xl font-bold text-green-600">₦{{ number_format($referralTask->worker_reward_per_task) }}</span>
                                        <span class="ml-1 text-sm text-green-600">per referral</span>
                                    </div>
                                    @auth
                                    <a href="{{ route('register', ['ref' => Auth::id()]) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                        <i class="fas fa-link mr-2"></i>Your Referral Link
                                    </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($availableTasks->count() > 0)
                        <div class="divide-y divide-gray-200">
                            @foreach($availableTasks as $task)
                                <div class="p-6 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <h3 class="text-md font-semibold text-gray-900 mr-2">{{ $task->title }}</h3>
                                                @if($task->is_featured)
                                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                                        <i class="fas fa-star mr-1"></i> Featured
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $task->description }}</p>
                                            <div class="flex items-center text-sm text-gray-500 space-x-4">
                                                <span><i class="fas fa-tag mr-1"></i> {{ $task->category->name ?? 'General' }}</span>
                                                <span><i class="fas fa-users mr-1"></i> {{ $task->quantity - $task->completed_count }}/{{ $task->quantity }} left</span>
                                                <span><i class="fas fa-clock mr-1"></i> {{ $task->expires_at ? $task->expires_at->diffForHumans() : 'No expiry' }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4 text-right">
                                            <div class="text-lg font-bold text-green-600 mb-1">
                                                ₦{{ number_format($task->worker_reward_per_task, 0) }}
                                            </div>
                                            <span class="text-xs text-gray-500">per task</span>
                                            <a href="{{ route('tasks.show', $task) }}" class="block mt-2 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                                                Start Task
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $availableTasks->links() }}
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <i class="fas fa-tasks text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">No tasks available at the moment</p>
                            <p class="text-sm text-gray-400 mt-2">Check back later for new opportunities</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="{{ route('tasks.index') }}" class="flex items-center p-3 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                            <i class="fas fa-search text-indigo-600 mr-3"></i>
                            <span class="text-indigo-700 font-medium">Browse Tasks</span>
                        </a>
                        <a href="{{ route('tasks.bundles') }}" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition">
                            <i class="fas fa-boxes text-green-600 mr-3"></i>
                            <span class="text-green-700 font-medium">Task Bundles</span>
                        </a>
                        <a href="{{ route('referrals.index') }}" class="flex items-center p-3 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                            <i class="fas fa-users text-yellow-600 mr-3"></i>
                            <span class="text-yellow-700 font-medium">Refer Friends</span>
                        </a>
                    </div>
                </div>

                <!-- My Submissions -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-900">My Submissions</h2>
                        <a href="{{ route('tasks.my-tasks') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                            View All
                        </a>
                    </div>

                    @if($mySubmissions->count() > 0)
                        <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                            @foreach($mySubmissions as $submission)
                                <div class="p-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $submission->task->title }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $submission->created_at->diffForHumans() }}</p>
                                        </div>
                                        <div class="ml-2">
                                            @if($submission->status === 'approved')
                                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                    <i class="fas fa-check mr-1"></i> Approved
                                                </span>
                                                <p class="text-xs font-bold text-green-600 mt-1">+₦{{ number_format($submission->reward, 0) }}</p>
                                            @elseif($submission->status === 'pending')
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                </span>
                                            @else
                                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                                    <i class="fas fa-times mr-1"></i> Rejected
                                                </span>
                                                @if($submission->rejection_reason)
                                                    <p class="text-xs text-red-600 mt-1">{{ $submission->rejection_reason }}</p>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <i class="fas fa-clipboard-list text-gray-300 text-3xl mb-2"></i>
                            <p class="text-gray-500 text-sm">No submissions yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
