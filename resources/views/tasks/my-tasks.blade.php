@extends('layouts.app')

@section('title', 'My Tasks - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">My Tasks</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Manage tasks you've created</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-plus mr-2"></i>
                    Create Task
                </a>
            </div>
        </div>

        <!-- Top Row: Created tasks + Submissions Received for owners -->
        <!-- Simplified tabbed interface to reduce clutter -->
        <style>
            /* hide native scrollbars to avoid left/right arrows appearing on hover */
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .tab-panel { display: none; }
            .tab-panel.active { display: block; }
        </style>

        <div class="mb-6">
            <div class="flex items-center space-x-3">
                <button id="tab-created" class="tab-btn px-4 py-2 rounded-lg bg-indigo-600 text-white font-semibold">Created Tasks</button>
                <button id="tab-received" class="tab-btn px-4 py-2 rounded-lg bg-gray-100 dark:bg-dark-800 text-gray-800 dark:text-gray-100">Submissions Received</button>
                <button id="tab-performed" class="tab-btn px-4 py-2 rounded-lg bg-gray-100 dark:bg-dark-800 text-gray-800 dark:text-gray-100">Tasks Performed</button>
                <div class="ml-auto text-sm text-gray-500">Created: {{ $createdTasks->total() }} • Received: {{ $receivedSubmissions->total() }} • Performed: {{ $completedTasks->total() }}</div>
            </div>
        </div>

        <!-- Created Tasks Panel -->
        <div id="panel-created" class="tab-panel active">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg overflow-hidden border border-gray-100 dark:border-dark-700">
                <div class="p-4 border-b border-gray-100 dark:border-dark-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Created Tasks</h3>
                        <p class="text-sm text-gray-500">Overview of tasks you've published</p>
                    </div>
                    <div class="text-sm text-gray-500">Total: {{ $createdTasks->total() }}</div>
                </div>

                <div class="no-scrollbar">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                        <thead class="bg-gray-50 dark:bg-dark-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Task</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reward</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-dark-900 divide-y divide-gray-200 dark:divide-dark-700">
                            @foreach($createdTasks as $task)
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $task->title }}</div>
                                        <div class="text-xs text-gray-500">{{ ucfirst($task->platform) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-bold text-green-600 dark:text-green-400">₦{{ number_format($task->worker_reward_per_task, 0) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-28 bg-gray-200 dark:bg-dark-700 rounded-full h-2 mr-2">
                                                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2 rounded-full" style="width: {{ ($task->task_completions_count / max(1, $task->quantity)) * 100 }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $task->task_completions_count }}/{{ $task->quantity }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($task->pending_submissions_count > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-700/20 text-amber-700 dark:text-amber-300">{{ $task->pending_submissions_count }} Pending</span>
                                        @else
                                            <span class="text-xs text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('tasks.show', $task) }}" class="p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-dark-700 rounded-lg transition-colors" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('tasks.analytics', $task) }}" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-700 rounded-lg transition-colors" title="Analytics">
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                            <a href="{{ route('tasks.edit', $task) }}" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-700 rounded-lg transition-colors" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4">
                    {{ $createdTasks->links() }}
                </div>
            </div>
        </div>

        <!-- Submissions Received Panel (with quick approve/reject) -->
        <div id="panel-received" class="tab-panel">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg overflow-hidden border border-gray-100 dark:border-dark-700 p-4">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Submissions Received</h3>
                        <p class="text-sm text-gray-500">Manage task submissions from workers</p>
                    </div>
                    <div class="text-sm text-gray-500">Total: {{ $receivedSubmissions->total() }}</div>
                </div>

                @if($receivedSubmissions->count() > 0)
                <div class="no-scrollbar">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                        <thead class="bg-gray-50 dark:bg-dark-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Task</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Worker</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reward</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-dark-900 divide-y divide-gray-200 dark:divide-dark-700">
                            @foreach($receivedSubmissions as $submission)
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $submission->task->title ?? '— deleted task —' }}</div>
                                        <div class="text-xs text-gray-500">{{ $submission->task->platform ? ucfirst($submission->task->platform) : '' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ $submission->user->name ?? '— deleted user —' }}</div>
                                        <div class="text-xs text-gray-500">{{ $submission->user->email ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($submission->isApproved())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-700/20 text-emerald-700 dark:text-emerald-300">Approved</span>
                                        @elseif($submission->isRejected())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-700/20 text-red-700 dark:text-red-300">Rejected</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-700/20 text-amber-700 dark:text-amber-300">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600 dark:text-green-400">₦{{ number_format($submission->reward_amount ?? 0, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            @if($submission->task)
                                            <a href="{{ route('tasks.show', $submission->task) }}" class="p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-dark-700 rounded-lg transition-colors" title="View Task">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endif

                                            <a href="{{ route('tasks.submission.review', $submission) }}" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-700 rounded-lg transition-colors" title="View Submission">
                                                <i class="fas fa-clipboard-list"></i>
                                            </a>

                                            @if($submission->isPending())
                                                {{-- Quick approve form --}}
                                                <form action="{{ route('tasks.submission.approve', $submission) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="p-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>

                                                {{-- Quick reject toggle --}}
                                                <button type="button" class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition" onclick="toggleRejectForm({{ $submission->id }})" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>

                                                <div id="reject-form-{{ $submission->id }}" class="mt-2 hidden">
                                                    <form action="{{ route('tasks.submission.reject', $submission) }}" method="POST">
                                                        @csrf
                                                        <div class="flex space-x-2">
                                                            <select name="reason" required class="px-3 py-2 bg-gray-100 dark:bg-dark-800 text-sm rounded-md">
                                                                <option value="">Reason...</option>
                                                                @foreach(\App\Models\TaskCompletion::REJECTION_REASONS as $key => $label)
                                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                            <input type="text" name="notes" placeholder="Notes (optional)" class="px-3 py-2 bg-gray-100 dark:bg-dark-800 rounded-md text-sm" />
                                                            <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-md">Send</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4">
                    {{ $receivedSubmissions->links() }}
                </div>
                @else
                <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                    No submissions received yet. Once workers start submitting tasks, they will appear here.
                </div>
                @endif
            </div>
        </div>

        <!-- Tasks Performed Panel -->
        <div id="panel-performed" class="tab-panel">
            <div class="mt-2">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Tasks Performed</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Tasks you've submitted or completed</p>
                    </div>
                </div>

                @if($completedTasks->count() > 0)
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
                    <div class="no-scrollbar">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                            <thead class="bg-gray-50 dark:bg-dark-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Task</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submitted</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reward</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-dark-900 divide-y divide-gray-200 dark:divide-dark-700">
                                @foreach($completedTasks as $completion)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $completion->task->title ?? '— deleted task —' }}</div>
                                            <div class="text-xs text-gray-500">{{ $completion->task->platform ? ucfirst($completion->task->platform) : '' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $completion->created_at->format('M d, Y h:i A') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($completion->isApproved())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-700/20 text-emerald-700 dark:text-emerald-300">Approved</span>
                                            @elseif($completion->isRejected())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-700/20 text-red-700 dark:text-red-300">Rejected</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-700/20 text-amber-700 dark:text-amber-300">Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600 dark:text-green-400">₦{{ number_format($completion->reward_amount ?? $completion->reward_earned ?? 0, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                @if($completion->task)
                                                <a href="{{ route('tasks.show', $completion->task) }}" class="p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-dark-700 rounded-lg transition-colors" title="View Task">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endif

                                                <a href="{{ route('tasks.submission.review', $completion) }}" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-700 rounded-lg transition-colors" title="View Submission">
                                                    <i class="fas fa-clipboard-list"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4">
                        {{ $completedTasks->links() }}
                    </div>
                </div>
                @else
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-12 text-center">
                    <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">No performed tasks yet</h3>
                    <p class="text-gray-500 dark:text-gray-400">Submit a task to see it listed here once it's processed.</p>
                </div>
                @endif
            </div>
        </div>
        <!-- end simplified panels -->

        <script>
            // Simple tab switching
            function showTab(tab) {
                document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
                document.getElementById('panel-' + tab).classList.add('active');

                // style buttons
                document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('bg-indigo-600','text-white'); b.classList.add('bg-gray-100','text-gray-800'); });
                var btn = document.getElementById('tab-' + tab);
                if (btn) { btn.classList.add('bg-indigo-600','text-white'); btn.classList.remove('bg-gray-100','text-gray-800'); }
            }

            document.getElementById('tab-created').addEventListener('click', function(){ showTab('created'); });
            document.getElementById('tab-received').addEventListener('click', function(){ showTab('received'); });
            document.getElementById('tab-performed').addEventListener('click', function(){ showTab('performed'); });

            function toggleRejectForm(id) {
                var el = document.getElementById('reject-form-' + id);
                if (!el) return;
                el.classList.toggle('hidden');
            }
        </script>
    </div>
</div>
@endsection
