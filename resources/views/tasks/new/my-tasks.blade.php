@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">My Tasks</h1>
        <a href="{{ route('new-tasks.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Create New Task
        </a>
    </div>

    <!-- Active/Pending Funding Tasks -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Active & Pending Tasks</h2>
        @if($activeTasks->isEmpty())
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <p class="text-gray-500">No active tasks.</p>
            </div>
        @else
            <div class="grid gap-4">
                @foreach($activeTasks as $task)
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-1 text-xs font-medium rounded 
                                        @switch($task->status)
                                            @case('pending_funding') bg-yellow-100 text-yellow-800 @break
                                            @case('active') bg-green-100 text-green-800 @break
                                            @case('paused') bg-gray-100 text-gray-800 @break
                                        @endswitch">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                                        {{ ucfirst($task->category) }}
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold">{{ $task->title }}</h3>
                                <div class="text-sm text-gray-500 mt-1">
                                    {{ $task->workers_accepted_count }}/{{ $task->max_workers }} workers • 
                                    ₦{{ number_format($task->reward_per_user, 2) }} each
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-green-600">
                                    ₦{{ number_format($task->escrow_balance, 2) }}
                                </div>
                                <div class="text-xs text-gray-500">Escrow Balance</div>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4 pt-4 border-t">
                            <a href="{{ route('new-tasks.show', $task->id) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Details
                            </a>
                            @if($task->status === 'pending_funding')
                                <form action="{{ route('new-tasks.fund', $task->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium ml-4">
                                        Fund Task
                                    </button>
                                </form>
                            @endif
                            @if($task->status === 'active')
                                <form action="{{ route('new-tasks.pause', $task->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-800 text-sm font-medium ml-4">
                                        Pause
                                    </button>
                                </form>
                            @endif
                            @if($task->status === 'paused')
                                <form action="{{ route('new-tasks.resume', $task->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium ml-4">
                                        Resume
                                    </button>
                                </form>
                            @endif
                            @if($task->status !== 'completed' && $task->status !== 'cancelled')
                                <form action="{{ route('new-tasks.cancel', $task->id) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('Are you sure you want to cancel this task?')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium ml-4">
                                        Cancel
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Draft Tasks -->
    @if(!$draftTasks->isEmpty())
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Draft Tasks</h2>
        <div class="grid gap-4">
            @foreach($draftTasks as $task)
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-gray-400">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $task->title }}</h3>
                            <div class="text-sm text-gray-500 mt-1">
                                {{ $task->workers_accepted_count }}/{{ $task->max_workers }} workers • 
                                ₦{{ number_format($task->reward_per_user, 2) }} each
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">
                            Draft
                        </span>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('new-tasks.show', $task->id) }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Continue Setup
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Completed Tasks -->
    @if(!$completedTasks->isEmpty())
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Completed Tasks</h2>
        <div class="grid gap-4">
            @foreach($completedTasks as $task)
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-400">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $task->title }}</h3>
                            <div class="text-sm text-gray-500 mt-1">
                                {{ $task->workers_accepted_count }}/{{ $task->max_workers }} workers completed
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">
                            Completed
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
