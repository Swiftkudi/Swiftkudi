@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('new-tasks.my-tasks') }}" class="text-blue-600 hover:text-blue-800">
            ← Back to My Tasks
        </a>
    </div>

    <h1 class="text-2xl font-bold mb-6">Submissions for: {{ $task->title }}</h1>

    <!-- Tabs -->
    <div class="mb-6">
        <div class="flex gap-2">
            <a href="#pending" class="px-4 py-2 rounded-lg bg-yellow-100 text-yellow-800 font-medium">
                Pending ({{ $pendingSubmissions->count() }})
            </a>
            <a href="#approved" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700">
                Approved ({{ $approvedSubmissions->count() }})
            </a>
            <a href="#rejected" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700">
                Rejected ({{ $rejectedSubmissions->count() }})
            </a>
        </div>
    </div>

    <!-- Pending Submissions -->
    <div class="mb-8" id="pending">
        <h2 class="text-xl font-semibold mb-4">Pending Submissions</h2>
        @if($pendingSubmissions->isEmpty())
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <p class="text-gray-500">No pending submissions.</p>
            </div>
        @else
            <div class="grid gap-4">
                @foreach($pendingSubmissions as $submission)
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-600 font-semibold">{{ substr($submission->worker->name ?? 'U', 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $submission->worker->name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-gray-500">{{ $submission->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <form action="{{ route('new-tasks.submissions.approve', [$task->id, $submission->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                        Approve
                                    </button>
                                </form>
                                <button
                                        type="button"
                                        data-submission-id="{{ $submission->id }}"
                                        class="js-reject-submission bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                                    Reject
                                </button>
                            </div>
                        </div>
                        @if($submission->notes)
                        <div class="mt-4 p-3 bg-gray-50 rounded">
                            <div class="text-sm text-gray-600">{{ $submission->notes }}</div>
                        </div>
                        @endif
                        @if($submission->proof_data)
                        <div class="mt-4">
                            <div class="text-sm font-medium mb-2">Proof Data:</div>
                            <pre class="bg-gray-50 p-3 rounded text-xs overflow-x-auto">{{ json_encode($submission->proof_data, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Approved Submissions -->
    <div class="mb-8" id="approved">
        <h2 class="text-xl font-semibold mb-4">Approved Submissions</h2>
        @if($approvedSubmissions->isEmpty())
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <p class="text-gray-500">No approved submissions.</p>
            </div>
        @else
            <div class="grid gap-4">
                @foreach($approvedSubmissions as $submission)
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-400">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                    <span class="text-green-600 font-semibold">✓</span>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $submission->worker->name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-gray-500">{{ $submission->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">
                                Approved
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Rejected Submissions -->
    <div id="rejected">
        <h2 class="text-xl font-semibold mb-4">Rejected Submissions</h2>
        @if($rejectedSubmissions->isEmpty())
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <p class="text-gray-500">No rejected submissions.</p>
            </div>
        @else
            <div class="grid gap-4">
                @foreach($rejectedSubmissions as $submission)
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-400">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                    <span class="text-red-600 font-semibold">✕</span>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $submission->worker->name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-gray-500">{{ $submission->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800">
                                Rejected
                            </span>
                        </div>
                        @if($submission->rejection_reason)
                        <div class="mt-3 p-3 bg-red-50 rounded">
                            <div class="text-sm text-red-600">Reason: {{ $submission->rejection_reason }}</div>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4">
        <h2 class="text-xl font-bold mb-4">Reject Submission</h2>
        <form id="reject-form">
            <input type="hidden" id="submission-id" name="submission_id">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Rejection Reason</label>
                <textarea name="reason" rows="4" class="w-full border rounded-lg p-3" required 
                          placeholder="Please explain why this submission is being rejected..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="hideRejectModal()" class="flex-1 bg-gray-200 text-gray-800 py-2 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                    Reject
                </button>
            </div>
        </form>
    </div>
</div>

<div
    id="new-task-submissions-config"
    class="hidden"
    data-base-path="{{ route('new-tasks.show', $task->id) }}"
></div>

<script>
const submissionsConfig = document.getElementById('new-task-submissions-config');
const submissionsBasePath = submissionsConfig?.dataset.basePath || '';

document.querySelectorAll('.js-reject-submission').forEach((button) => {
    button.addEventListener('click', () => {
        const submissionId = button.dataset.submissionId;
        if (submissionId) {
            showRejectModal(submissionId);
        }
    });
});

function showRejectModal(submissionId) {
    document.getElementById('submission-id').value = submissionId;
    document.getElementById('reject-modal').classList.remove('hidden');
}

function hideRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
}

document.getElementById('reject-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const submissionId = formData.get('submission_id');
    
    try {
        const response = await fetch(`${submissionsBasePath}/submissions/${submissionId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                reason: formData.get('reason')
            })
        });
        
        const data = await response.json();
        if(data.success) {
            window.location.reload();
        } else {
            if ((response.status === 422 || data.errors || data.error_list) && window.SwiftkudiFormFeedback) {
                window.SwiftkudiFormFeedback.showValidationErrors(form, data, {
                    boxId: 'new-task-reject-error-box',
                });
            } else {
                alert(data.message || 'Failed to reject submission');
            }
        }
    } catch(err) {
        if (window.SwiftkudiFormFeedback) {
            window.SwiftkudiFormFeedback.showValidationErrors(form, {
                message: 'An error occurred while rejecting this submission. Please try again.',
            }, {
                boxId: 'new-task-reject-error-box',
            });
        } else {
            alert('An error occurred');
        }
    }
});
</script>
@endsection
