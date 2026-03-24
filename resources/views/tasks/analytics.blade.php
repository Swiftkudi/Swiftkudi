@extends('layouts.app')

@section('title', 'Task Analytics - SwiftKudi')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('tasks.my-tasks') }}" class="text-indigo-400 hover:text-indigo-300 mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Back to My Tasks
        </a>

        <div class="bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-1">Analytics for: {{ $task->title }}</h1>
                    <p class="text-gray-400">Basic metrics and submission history for this task</p>
                </div>
                <div class="text-sm text-gray-300">Created: {{ $task->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-dark-900 rounded-lg p-4 border">
                <div class="text-sm text-gray-500">Total Submissions</div>
                <div class="text-2xl font-bold">{{ $stats['total_submissions'] ?? $completions->count() }}</div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-lg p-4 border">
                <div class="text-sm text-gray-500">Pending</div>
                <div class="text-2xl font-bold">{{ $stats['pending'] ?? $completions->where('status','pending')->count() }}</div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-lg p-4 border">
                <div class="text-sm text-gray-500">Approved</div>
                <div class="text-2xl font-bold">{{ $stats['approved'] ?? $completions->where('status','approved')->count() }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-dark-900 rounded-lg p-4 border mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Submissions</h3>
            @if($completions->count() > 0)
                <div class="divide-y divide-gray-200 dark:divide-dark-700">
                    @foreach($completions as $c)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $c->user->name ?? '— user' }}</div>
                                <div class="text-xs text-gray-500">{{ $c->created_at->format('M d, Y h:i A') }} • {{ ucfirst($c->status) }}</div>
                            </div>
                            <div class="text-sm text-green-600 font-bold">₦{{ number_format($c->reward_amount ?? $c->reward_earned ?? 0, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center text-gray-500">No submissions yet for this task.</div>
            @endif
        </div>

        <div class="text-right">
            <a href="{{ route('tasks.my-tasks') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md">Back</a>
        </div>
    </div>
</div>
@endsection
