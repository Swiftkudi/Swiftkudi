@extends('layouts.app')

@section('title', 'My Job Posts - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent">My Job Posts</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Manage your posted jobs</p>
            </div>
            <a href="{{ route('jobs.create') }}" class="px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                <i class="fas fa-plus mr-2"></i>Post New Job
            </a>
        </div>

        @if($jobs->count() > 0)
            <div class="space-y-4">
                @foreach($jobs as $job)
                    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $job->title }}</h3>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                                        @if($job->status === 'active') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400
                                        @elseif($job->status === 'closed') bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400
                                        @else bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 @endif">
                                        {{ ucfirst($job->status) }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if($job->category)
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-tag"></i>{{ $job->category->name }}
                                        </span>
                                    @endif
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-money-bill"></i>{{ $job->budget_range }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-users"></i>{{ $job->applications_count ?? 0 }} applications
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-clock"></i>Posted {{ $job->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('jobs.show', $job) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('jobs.edit', $job) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($job->status === 'active')
                                    <form action="{{ route('jobs.close', $job) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-yellow-500 hover:text-yellow-600 transition-colors" onclick="return confirm('Are you sure you want to close this job?')">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Applications Preview -->
                        @if($job->applications->count() > 0)
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-dark-700">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Recent Applications</h4>
                                <div class="space-y-2">
                                    @foreach($job->applications->take(3) as $application)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-800 rounded-xl">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center">
                                                    <span class="text-cyan-600 dark:text-cyan-400 text-sm font-medium">{{ substr($application->user->name, 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $application->user->name }}</div>
                                                    <div class="text-xs text-gray-500">₦{{ number_format($application->proposal_amount) }}</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-1 text-xs rounded-full 
                                                    @if($application->status === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400
                                                    @elseif($application->status === 'hired') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400
                                                    @else bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400 @endif">
                                                    {{ $application->status_label }}
                                                </span>
                                                @if($application->status === 'pending')
                                                    <form action="{{ route('jobs.hire', $application) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                                            Hire
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if($job->applications->count() > 3)
                                    <a href="#" class="mt-3 inline-flex items-center text-sm text-cyan-600 dark:text-cyan-400 hover:underline">
                                        View all {{ $job->applications->count() }} applications
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $jobs->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-12 text-center">
                <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-briefcase text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">No job posts yet</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Start by posting your first job</p>
                <a href="{{ route('jobs.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                    <i class="fas fa-plus mr-2"></i>Post Your First Job
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
