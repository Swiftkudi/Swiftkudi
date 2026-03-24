@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Work Opportunities</h1>
    </div>

    <!-- Search & Filters -->
    <div class="mb-6">
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ $search ?? '' }}" 
                   placeholder="Search tasks..." 
                   class="flex-1 border rounded-lg px-4 py-2">
            <select name="category" class="border rounded-lg px-4 py-2">
                <option value="">All Categories</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Search
            </button>
        </form>
    </div>

    <!-- Category Pills -->
    <div class="flex gap-2 mb-6">
        <a href="{{ route('new-tasks.work') }}" 
           class="px-4 py-2 rounded-lg {{ !$category ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
            All
        </a>
        @foreach($categories as $key => $label)
            <a href="{{ route('new-tasks.work', ['category' => $key]) }}" 
               class="px-4 py-2 rounded-lg {{ $category === $key ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Tasks List -->
    @if($tasks->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">No tasks available right now.</p>
            <p class="text-gray-400">Check back later!</p>
        </div>
    @else
        <div class="grid gap-4">
            @foreach($tasks as $task)
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-1 text-xs font-medium rounded 
                                    @switch($task->category)
                                        @case('micro') bg-green-100 text-green-800 @break
                                        @case('ugc') bg-purple-100 text-purple-800 @break
                                        @case('growth') bg-yellow-100 text-yellow-800 @break
                                        @case('premium') bg-indigo-100 text-indigo-800 @break
                                    @endswitch">
                                    {{ ucfirst($task->category) }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ $task->workers_accepted_count }}/{{ $task->max_workers }} workers
                                </span>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">{{ $task->title }}</h3>
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $task->description }}</p>
                        </div>
                        <div class="text-right ml-4">
                            <div class="text-xl font-bold text-green-600">₦{{ number_format($task->reward_per_user, 2) }}</div>
                            <div class="text-sm text-gray-500">per submission</div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-4 pt-4 border-t">
                        <span class="text-sm text-gray-500">
                            Posted {{ $task->created_at->diffForHumans() }}
                        </span>
                        <a href="{{ route('new-tasks.show', $task->id) }}" 
                           class="text-blue-600 hover:text-blue-800 font-medium">
                            View & Submit →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
@endsection
