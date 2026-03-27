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
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-user-check"></i>{{ $job->hired_count }}/{{ $job->positions_available }} hired
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('jobs.show', $job) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('jobs.edit', $job) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($job->status === 'active')
                                    <form action="{{ route('jobs.close', $job) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-yellow-500 hover:text-yellow-600 transition-colors" title="Close Job" onclick="return confirm('Are you sure you want to close this job?')">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('jobs.destroy', $job) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:text-red-600 transition-colors" title="Delete" onclick="return confirm('Are you sure you want to delete this job? This cannot be undone.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
                                                    @elseif($application->status === 'rejected') bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400
                                                    @else bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400 @endif">
                                                    {{ $application->status_label }}
                                                </span>
                                                @if($application->status === 'pending' && !$job->is_fully_hired)
                                                    <button type="button" onclick="openReviewModal({{ $application->id }}, '{{ addslashes($application->user->name) }}', '{{ $application->user->email }}', '{{ $application->created_at->diffForHumans() }}', '{{ addslashes($application->cover_letter ?? '') }}', {{ $application->proposal_amount }}, '{{ addslashes($application->estimated_duration ?? 'N/A') }}', {{ $job->hired_count }}, {{ $job->positions_available }})" class="px-3 py-1 text-xs bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg">
                                                        <i class="fas fa-eye mr-1"></i>Review
                                                    </button>
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

<!-- Review Application Modal -->
<div id="reviewModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeReviewModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-dark-700 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Review Application</h3>
                    <button type="button" onclick="closeReviewModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Applicant Info -->
                <div class="flex items-center gap-4 mb-6 p-4 bg-gray-50 dark:bg-dark-800 rounded-xl">
                    <div class="w-12 h-12 rounded-full bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center">
                        <span id="modalApplicantInitial" class="text-cyan-600 dark:text-cyan-400 text-lg font-bold"></span>
                    </div>
                    <div>
                        <div id="modalApplicantName" class="text-lg font-semibold text-gray-900 dark:text-gray-100"></div>
                        <div id="modalApplicantEmail" class="text-sm text-gray-500 dark:text-gray-400"></div>
                    </div>
                    <div class="ml-auto text-right">
                        <div id="modalAppliedAt" class="text-xs text-gray-500 dark:text-gray-400"></div>
                    </div>
                </div>

                <!-- Application Details -->
                <div class="space-y-4 mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-dark-800 rounded-xl">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Proposal Amount</div>
                            <div id="modalProposalAmount" class="text-lg font-bold text-gray-900 dark:text-gray-100"></div>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-dark-800 rounded-xl">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Estimated Duration</div>
                            <div id="modalDuration" class="text-lg font-bold text-gray-900 dark:text-gray-100"></div>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cover Letter</div>
                        <div id="modalCoverLetter" class="p-4 bg-gray-50 dark:bg-dark-800 rounded-xl text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line max-h-48 overflow-y-auto"></div>
                    </div>
                </div>

                <!-- Employer Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-sticky-note mr-1"></i>Notes / Terms for Applicant
                    </label>
                    <textarea id="modalEmployerNotes" rows="4" placeholder="Add any notes, expectations, or terms before hiring this applicant..."
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent text-sm"></textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This will be visible to the applicant after hiring.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <form id="rejectForm" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-6 py-3 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 font-medium rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            <i class="fas fa-times mr-2"></i>Reject
                        </button>
                    </form>
                    <form id="hireForm" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="employer_notes" id="hireEmployerNotes">
                        <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition-colors">
                            <i class="fas fa-check mr-2"></i>Hire Applicant
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var currentHiredCount = 0;
var currentPositionsAvailable = 1;

function openReviewModal(id, name, email, appliedAt, coverLetter, proposalAmount, duration, hiredCount, positionsAvailable) {
    document.getElementById('modalApplicantInitial').textContent = name.charAt(0).toUpperCase();
    document.getElementById('modalApplicantName').textContent = name;
    document.getElementById('modalApplicantEmail').textContent = email;
    document.getElementById('modalAppliedAt').textContent = 'Applied ' + appliedAt;
    document.getElementById('modalCoverLetter').textContent = coverLetter || 'No cover letter provided.';
    document.getElementById('modalProposalAmount').textContent = '\u20a6' + Number(proposalAmount).toLocaleString();
    document.getElementById('modalDuration').textContent = duration;
    document.getElementById('modalEmployerNotes').value = '';
    document.getElementById('hireForm').action = '{{ url("jobs/applications") }}/' + id + '/hire';
    document.getElementById('rejectForm').action = '{{ url("jobs/applications") }}/' + id + '/reject';
    currentHiredCount = hiredCount;
    currentPositionsAvailable = positionsAvailable;
    document.getElementById('reviewModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.getElementById('hireForm').addEventListener('submit', function(e) {
    document.getElementById('hireEmployerNotes').value = document.getElementById('modalEmployerNotes').value;
    var remaining = currentPositionsAvailable - currentHiredCount - 1;
    var message = 'Are you sure you want to hire this applicant?';
    if (remaining <= 0) {
        message += ' This will fill all remaining positions. All pending applications will be rejected.';
    } else {
        message += ' You still have ' + remaining + ' position(s) left to fill.';
    }
    if (!confirm(message)) {
        e.preventDefault();
    }
});

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    if (!confirm('Are you sure you want to reject this application?')) {
        e.preventDefault();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeReviewModal();
});
</script>
@endsection
