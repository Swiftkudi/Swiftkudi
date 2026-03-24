@extends('layouts.admin')

@section('title', 'Tasks')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Manage Tasks</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">View and manage all tasks</p>
            </div>
            <a href="{{ route('admin.index') }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 mb-8">
            <form action="{{ route('admin.tasks') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl shadow-lg shadow-indigo-500/30">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Tasks Table -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
            <form id="bulk-form-tasks" action="{{ route('admin.tasks.bulk-delete') }}" method="POST">@csrf</form>
            <div id="bulk-toolbar-tasks" class="hidden px-6 py-3 bg-red-100 dark:bg-red-500/10 border-b border-red-200 dark:border-red-500/20 flex items-center justify-between">
                <span class="text-sm text-red-600 dark:text-red-400 font-medium"><span id="bulk-count-tasks">0</span> selected</span>
                <button type="button" onclick="submitBulkDelete('tasks')" class="px-4 py-1.5 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-500/30 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-trash mr-2"></i>Delete Selected
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                    <thead class="bg-gray-50 dark:bg-dark-800">
                        <tr>
                            <th class="px-4 py-4 w-10">
                                <input type="checkbox" id="select-all-tasks" class="bulk-select-all w-4 h-4 rounded cursor-pointer" data-target="bulk-cb-tasks">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Task</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Owner</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Platform</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Reward</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Progress</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Created</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-dark-900 divide-y divide-gray-200 dark:divide-dark-700">
                        @foreach($tasks as $task)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                            <td class="px-4 py-4">
                                <input type="checkbox" name="ids[]" value="{{ $task->id }}" class="bulk-cb-tasks w-4 h-4 rounded cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ Str::limit($task->title, 40) }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $task->category->name ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr($task->user->name, 0, 2)) }}
                                    </div>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $task->user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="capitalize text-sm text-gray-900 dark:text-gray-100">{{ $task->platform }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-green-600 dark:text-green-400">₦{{ number_format($task->worker_reward_per_task, 0) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 dark:bg-dark-700 rounded-full h-2 mr-2">
                                        @if($task->quantity > 0 && $task->task_completions_count > 0)
                                        <div class="bg-indigo-500 h-2 rounded-full w-full"></div>
                                        @else
                                        <div class="bg-indigo-500 h-2 rounded-full w-0"></div>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $task->task_completions_count }}/{{ $task->quantity }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($task->status === 'active')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">
                                    <i class="fas fa-check-circle mr-1"></i>Active
                                </span>
                                @elseif($task->status === 'completed')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400">
                                    <i class="fas fa-check mr-1"></i>Completed
                                </span>
                                @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300">
                                    {{ ucfirst($task->status) }}
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $task->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('admin.tasks.delete', $task) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-500/30 transition-colors text-sm" onclick="return confirm('Delete this task? This action cannot be undone.')">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-700">
                {{ $tasks->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
