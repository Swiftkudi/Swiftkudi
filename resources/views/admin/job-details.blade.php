@extends('layouts.admin')

@section('title', 'Job Details')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.jobs') }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                    <i class="fas fa-arrow-left mr-2"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Job Details</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">View job information and applications</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.jobs.delete', $job) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-500/30 transition-colors" onclick="return confirm('Delete this job? This action cannot be undone.')">
                        <i class="fas fa-trash mr-2"></i>Delete Job
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Job Info -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $job->title }}</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Description</h3>
                            <p class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $job->description }}</p>
                        </div>

                        @if($job->requirements)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Requirements</h3>
                            <p class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $job->requirements }}</p>
                        </div>
                        @endif

                        @if($job->benefits)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Benefits</h3>
                            <p class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $job->benefits }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Applications -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">
                        <i class="fas fa-users mr-2 text-indigo-500"></i>Applications ({{ $applications->count() }})
                    </h3>
                    
                    @if($applications->count() > 0)
                    <div class="space-y-4">
                        @foreach($applications as $application)
                        <div class="border border-gray-200 dark:border-dark-700 rounded-xl p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold">
                                        {{ strtoupper(substr($application->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $application->user->name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $application->user->email }}</p>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $application->created_at->diffForHumans() }}
                                </span>
                            </div>
                            @if($application->cover_letter)
                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-dark-700">
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($application->cover_letter, 200) }}</p>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400">No applications yet</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 sticky top-8">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Job Information</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Status</span>
                            @if($job->status === 'active' && !$job->is_expired)
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">
                                <i class="fas fa-check-circle mr-1"></i>Active
                            </span>
                            @elseif($job->is_expired)
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400">
                                <i class="fas fa-clock mr-1"></i>Expired
                            </span>
                            @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300">
                                {{ ucfirst($job->status) }}
                            </span>
                            @endif
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Category</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $job->category->name ?? 'N/A' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Job Type</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $job->type_label }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Experience Level</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $job->level_label }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Budget</span>
                            <span class="font-semibold text-green-600 dark:text-green-400">{{ $job->budget_range }}</span>
                        </div>

                        @if($job->duration)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Duration</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $job->duration }}</span>
                        </div>
                        @endif

                        @if($job->location)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Location</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $job->location }}</span>
                        </div>
                        @endif

                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Views</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($job->views_count) }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Applications</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($job->applications_count) }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-dark-700">
                            <span class="text-gray-500 dark:text-gray-400">Created</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $job->created_at->format('M d, Y') }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-500 dark:text-gray-400">Expires</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $job->expires_at ? $job->expires_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Owner Info -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-dark-700">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Posted By</h4>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-lg font-bold">
                                {{ strtoupper(substr($job->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $job->user->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $job->user->email }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.user-details', $job->user) }}" class="mt-3 inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                            <i class="fas fa-external-link-alt mr-2"></i>View User Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
