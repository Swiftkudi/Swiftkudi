@extends('layouts.admin')

@section('title', 'Jobs')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Manage Jobs</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">View and manage all job postings</p>
            </div>
            <a href="{{ route('admin.index') }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6 mb-8">
            <form action="{{ route('admin.jobs') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or description..."
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl shadow-lg shadow-indigo-500/30">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Jobs Table -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                    <thead class="bg-gray-50 dark:bg-dark-800">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Job</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Owner</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Budget</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Applications</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Expires</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-dark-900 divide-y divide-gray-200 dark:divide-dark-700">
                        @forelse($jobs as $job)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.job-details', $job) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                    {{ Str::limit($job->title, 40) }}
                                </a>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $job->category->name ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr($job->user->name, 0, 2)) }}
                                    </div>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $job->user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="capitalize text-sm text-gray-900 dark:text-gray-100">{{ $job->type_label }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-green-600 dark:text-green-400">{{ $job->budget_range }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $job->applications_count }}</span>
                            </td>
                            <td class="px-6 py-4">
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
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $job->expires_at ? $job->expires_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.job-details', $job) }}" class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-400 rounded-lg hover:bg-indigo-200 dark:hover:bg-indigo-500/30 transition-colors text-sm">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                    <form action="{{ route('admin.jobs.delete', $job) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-500/30 transition-colors text-sm" onclick="return confirm('Delete this job? This action cannot be undone.')">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-briefcase text-4xl mb-4 opacity-50"></i>
                                    <p class="text-lg font-medium">No jobs found</p>
                                    <p class="text-sm">There are no job postings matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($jobs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-700">
                {{ $jobs->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
