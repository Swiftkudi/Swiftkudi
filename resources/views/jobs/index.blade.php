@extends('layouts.app')

@section('title', 'Job Board - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent">Job Board</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Find your next opportunity or hire talent</p>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6 mb-8">
            <form action="{{ route('jobs.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Job title or keywords..." 
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                        <select name="category" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Job Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Job Type</label>
                        <select name="type" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            <option value="">All Types</option>
                            <option value="full-time" {{ request('type') == 'full-time' ? 'selected' : '' }}>Full Time</option>
                            <option value="part-time" {{ request('type') == 'part-time' ? 'selected' : '' }}>Part Time</option>
                            <option value="contract" {{ request('type') == 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="internship" {{ request('type') == 'internship' ? 'selected' : '' }}>Internship</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <!-- Experience Level -->
                        <select name="level" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            <option value="">Any Experience</option>
                            <option value="entry" {{ request('level') == 'entry' ? 'selected' : '' }}>Entry Level</option>
                            <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="expert" {{ request('level') == 'expert' ? 'selected' : '' }}>Expert</option>
                        </select>

                        <!-- Sort -->
                        <select name="sort" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="budget_high" {{ request('sort') == 'budget_high' ? 'selected' : '' }}>Budget: High to Low</option>
                            <option value="budget_low" {{ request('sort') == 'budget_low' ? 'selected' : '' }}>Budget: Low to High</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white font-medium rounded-xl transition-colors">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <a href="{{ route('jobs.create') }}" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                            <i class="fas fa-plus mr-2"></i>Post a Job
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results -->
        @if($jobs->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($jobs as $job)
                    <a href="{{ route('jobs.show', $job) }}" class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6 hover:shadow-xl hover:border-cyan-300 dark:hover:border-cyan-500 transition-all transform hover:-translate-y-1">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-1">{{ $job->title }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $job->user->name }}</p>
                            </div>
                            @if($job->category)
                                <span class="px-3 py-1 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400 text-xs font-medium rounded-full">
                                    {{ $job->category->name }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-400 text-xs rounded-lg">
                                <i class="fas fa-briefcase mr-1"></i>{{ $job->type_label }}
                            </span>
                            <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-400 text-xs rounded-lg">
                                <i class="fas fa-layer-group mr-1"></i>{{ $job->level_label }}
                            </span>
                            @if($job->location)
                                <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-400 text-xs rounded-lg">
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $job->location }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="text-lg font-bold text-cyan-600 dark:text-cyan-400">
                                {{ $job->budget_range }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <i class="fas fa-clock mr-1"></i>{{ $job->expires_at->diffForHumans() }}
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-dark-700 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span><i class="fas fa-eye mr-1"></i>{{ $job->views_count ?? 0 }} views</span>
                            <span><i class="fas fa-users mr-1"></i>{{ $job->applications_count ?? 0 }} applications</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $jobs->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-12 text-center">
                <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-briefcase text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">No jobs found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Try adjusting your search filters or post a new job</p>
                <a href="{{ route('jobs.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                    <i class="fas fa-plus mr-2"></i>Post a Job
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
