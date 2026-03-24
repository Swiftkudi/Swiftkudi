@extends('layouts.app')

@section('title', $job->title . ' - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Job Header -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6 mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ $job->title }}</h1>
                            <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-building"></i>{{ $job->user->name }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-clock"></i>Posted {{ $job->created_at->diffForHumans() }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-eye"></i>{{ $job->views_count ?? 0 }} views
                                </span>
                            </div>
                        </div>
                        @if($job->category)
                            <span class="px-4 py-2 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400 text-sm font-medium rounded-xl">
                                {{ $job->category->name }}
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-3 mb-6">
                        <span class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-sm rounded-lg">
                            <i class="fas fa-briefcase mr-1"></i>{{ $job->type_label }}
                        </span>
                        <span class="px-3 py-1.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 text-sm rounded-lg">
                            <i class="fas fa-layer-group mr-1"></i>{{ $job->level_label }}
                        </span>
                        @if($job->location)
                            <span class="px-3 py-1.5 bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 text-sm rounded-lg">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $job->location }}
                            </span>
                        @endif
                        @if($job->duration)
                            <span class="px-3 py-1.5 bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 text-sm rounded-lg">
                                <i class="fas fa-calendar mr-1"></i>{{ $job->duration }}
                            </span>
                        @endif
                    </div>

                    <div class="text-2xl font-bold text-cyan-600 dark:text-cyan-400 mb-6">
                        {{ $job->budget_range }}
                    </div>

                    <!-- Description -->
                    <div class="prose dark:prose-invert max-w-none">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Job Description</h3>
                        <div class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $job->description }}</div>
                    </div>

                    @if($job->requirements)
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Requirements</h3>
                        <div class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $job->requirements }}</div>
                    </div>
                    @endif

                    @if($job->benefits)
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Benefits</h3>
                        <div class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $job->benefits }}</div>
                    </div>
                    @endif
                </div>

                <!-- Apply Section -->
                @auth
                    @if(Auth::id() !== $job->user_id)
                        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                            @if($hasApplied)
                                <div class="text-center py-4">
                                    <div class="w-16 h-16 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-check text-yellow-600 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Application Submitted</h3>
                                    <p class="text-gray-500 dark:text-gray-400">You have already applied for this job. The employer will review your application.</p>
                                </div>
                            @else
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Apply for this Job</h3>
                                <form action="{{ route('jobs.apply', $job) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cover Letter</label>
                                        <textarea name="cover_letter" rows="5" required
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                            placeholder="Explain why you're the best fit for this job..."></textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Your Proposal (₦)</label>
                                            <input type="number" name="proposal_amount" required min="0" step="100"
                                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                                placeholder="Enter your price">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estimated Duration</label>
                                            <input type="text" name="estimated_duration" required
                                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                                placeholder="e.g., 2 weeks, 1 month">
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-semibold rounded-xl transition-colors">
                                        <i class="fas fa-paper-plane mr-2"></i>Submit Application
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6 text-center">
                        <p class="text-gray-500 dark:text-gray-400 mb-4">Please login to apply for this job</p>
                        <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 bg-cyan-600 hover:bg-cyan-700 text-white font-medium rounded-xl transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login to Apply
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Employer Card -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">About the Employer</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center">
                            <span class="text-cyan-600 dark:text-cyan-400 font-bold text-lg">{{ substr($job->user->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $job->user->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Member since {{ $job->user->created_at->format('M Y') }}</div>
                        </div>
                    </div>
                    <a href="#" class="block w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 hover:bg-gray-200 dark:hover:bg-dark-700 text-center text-gray-700 dark:text-gray-300 rounded-xl transition-colors">
                        <i class="fas fa-user mr-2"></i>View Profile
                    </a>
                </div>

                <!-- Job Summary -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Job Summary</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Job Type</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $job->type_label }}</span>
                        </li>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Experience</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $job->level_label }}</span>
                        </li>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Budget</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $job->budget_range }}</span>
                        </li>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Posted</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $job->created_at->diffForHumans() }}</span>
                        </li>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Expires</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $job->expires_at->diffForHumans() }}</span>
                        </li>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Applications</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $job->applications_count ?? 0 }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Related Jobs -->
                @if($relatedJobs->count() > 0)
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Related Jobs</h3>
                    <div class="space-y-4">
                        @foreach($relatedJobs as $relatedJob)
                            <a href="{{ route('jobs.show', $relatedJob) }}" class="block p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                                <div class="font-medium text-gray-900 dark:text-gray-100 text-sm mb-1">{{ $relatedJob->title }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $relatedJob->budget_range }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
