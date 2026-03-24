@extends('layouts.app')

@section('title', 'My Applications - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent">My Applications</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Track your job applications</p>
        </div>

        @if($applications->count() > 0)
            <div class="space-y-4">
                @foreach($applications as $application)
                    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $application->job->title }}</h3>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                                        @if($application->status === 'pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400
                                        @elseif($application->status === 'hired') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400
                                        @elseif($application->status === 'rejected') bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400
                                        @else bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400 @endif">
                                        {{ $application->status_label }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-building"></i>{{ $application->job->user->name }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-money-bill"></i>₦{{ number_format($application->proposal_amount) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-clock"></i>{{ $application->estimated_duration }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-calendar"></i>Applied {{ $application->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('jobs.show', $application->job) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($application->status === 'pending')
                                    <form action="{{ route('jobs.withdraw', $application) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-red-500 hover:text-red-600 transition-colors" onclick="return confirm('Are you sure you want to withdraw this application?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if($application->cover_letter)
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-dark-700">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cover Letter</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ $application->cover_letter }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $applications->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-12 text-center">
                <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-paper-plane text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">No applications yet</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Start applying for jobs that match your skills</p>
                <a href="{{ route('jobs.index') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                    <i class="fas fa-search mr-2"></i>Browse Jobs
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
