@extends('layouts.app')

@section('title', 'Available Tasks - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Available Tasks</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Complete tasks and earn money</p>
        </div>

        <!-- Task Type Groups Quick Filters -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('tasks.index') }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ !request('task_group') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-dark-600' }}">
                All Tasks
            </a>
            <a href="{{ route('tasks.index', ['task_group' => 'micro']) }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ request('task_group') === 'micro' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-dark-600' }}">
                <i class="fas fa-bolt mr-1"></i> Micro Tasks
            </a>
            <a href="{{ route('tasks.index', ['task_group' => 'ugc']) }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ request('task_group') === 'ugc' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-dark-600' }}">
                <i class="fas fa-video mr-1"></i> UGC Tasks
            </a>
            <a href="{{ route('tasks.index', ['task_group' => 'referral']) }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ request('task_group') === 'referral' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-dark-600' }}">
                <i class="fas fa-users mr-1"></i> Referral Tasks
            </a>
            <a href="{{ route('tasks.index', ['task_group' => 'premium']) }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ request('task_group') === 'premium' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-dark-600' }}">
                <i class="fas fa-crown mr-1"></i> Premium Tasks
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 mb-8">
            <form action="{{ route('tasks.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <!-- Task Group Filter -->
                <input type="hidden" name="task_group" value="{{ request('task_group') }}">
                
                <!-- Task Type Filter -->
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Task Type</label>
                    <select name="task_type" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Task Types</option>
                        @if(isset($categoriesByGroup['micro']))
                        <optgroup label="Micro Tasks" class="font-semibold">
                            @foreach($categoriesByGroup['micro'] as $cat)
                            <option value="{{ $cat->id }}" {{ request('task_type') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                        @if(isset($categoriesByGroup['ugc']))
                        <optgroup label="UGC Tasks" class="font-semibold">
                            @foreach($categoriesByGroup['ugc'] as $cat)
                            <option value="{{ $cat->id }}" {{ request('task_type') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                        @if(isset($categoriesByGroup['referral']))
                        <optgroup label="Referral Tasks" class="font-semibold">
                            @foreach($categoriesByGroup['referral'] as $cat)
                            <option value="{{ $cat->id }}" {{ request('task_type') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                        @if(isset($categoriesByGroup['premium']))
                        <optgroup label="Premium Tasks" class="font-semibold">
                            @foreach($categoriesByGroup['premium'] as $cat)
                            <option value="{{ $cat->id }}" {{ request('task_type') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                    </select>
                </div>

                <!-- Platform Filter -->
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Platform</label>
                    <select name="platform" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Platforms</option>
                        <option value="instagram" {{ request('platform') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                        <option value="twitter" {{ request('platform') === 'twitter' ? 'selected' : '' }}>Twitter/X</option>
                        <option value="tiktok" {{ request('platform') === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                        <option value="facebook" {{ request('platform') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                        <option value="youtube" {{ request('platform') === 'youtube' ? 'selected' : '' }}>YouTube</option>
                        <option value="whatsapp" {{ request('platform') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="telegram" {{ request('platform') === 'telegram' ? 'selected' : '' }}>Telegram</option>
                        <option value="linkedin" {{ request('platform') === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                    </select>
                </div>

                <!-- Min Reward Filter -->
                <div class="flex-1 min-w-[120px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Min Reward</label>
                    <select name="min_reward" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Any</option>
                        <option value="50" {{ request('min_reward') == 50 ? 'selected' : '' }}>₦50+</option>
                        <option value="100" {{ request('min_reward') == 100 ? 'selected' : '' }}>₦100+</option>
                        <option value="250" {{ request('min_reward') == 250 ? 'selected' : '' }}>₦250+</option>
                        <option value="500" {{ request('min_reward') == 500 ? 'selected' : '' }}>₦500+</option>
                        <option value="1000" {{ request('min_reward') == 1000 ? 'selected' : '' }}>₦1,000+</option>
                    </select>
                </div>

                <!-- Apply Button -->
                <div>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-xl shadow-lg shadow-indigo-500/30 transition-all">
                        <i class="fas fa-filter mr-2"></i>Apply
                    </button>
                </div>
            </form>
        </div>

        <!-- Tasks Count -->
        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Showing <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $tasks->count() }}</span> tasks
                @if(request('task_group'))
                    in <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ ucfirst(request('task_group')) }}</span>
                @endif
            </p>
        </div>

        <!-- Tasks Grid -->
        @if(count($tasks) > 0)
        <div class="grid gap-4">
            @foreach($tasks as $task)
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl transition-all">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Task Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $task->task_group === 'micro' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : ($task->task_group === 'ugc' ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400' : ($task->task_group === 'referral' ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400' : 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400')) }}">
                                {{ ucfirst($task->task_group) }}
                            </span>
                            @if($task->is_featured)
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-amber-400 to-orange-400 text-white">
                                <i class="fas fa-star mr-1"></i>Featured
                            </span>
                            @endif
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 capitalize">
                                <i class="fab fa-{{ $task->platform }} mr-1"></i>{{ $task->platform }}
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $task->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ Str::limit($task->description, 100) }}</p>
                        <div class="flex items-center gap-4 mt-3 text-sm text-gray-500 dark:text-gray-400">
                            <span><i class="fas fa-clock mr-1"></i>{{ $task->created_at->diffForHumans() }}</span>
                            <span><i class="fas fa-clipboard-check mr-1"></i>{{ $task->task_completions_count }}/{{ $task->quantity }} done</span>
                        </div>
                    </div>
                    
                    <!-- Reward & Action -->
                    <div class="flex flex-row md:flex-col items-center md:items-end gap-4">
                        <div class="text-right">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">₦{{ number_format($task->worker_reward_per_task, 0) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">per task</div>
                        </div>
                        <a href="{{ route('tasks.show', $task) }}" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-xl shadow-lg shadow-indigo-500/30 transition-all whitespace-nowrap">
                            View Task
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $tasks->appends(request()->query())->links() }}
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-tasks text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">No tasks available</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Check back later for new tasks or try different filters</p>
            <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl transition-all">
                <i class="fas fa-refresh mr-2"></i>View All Tasks
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
