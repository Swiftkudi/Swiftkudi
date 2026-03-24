@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('new-tasks.index') }}" class="text-blue-600 hover:text-blue-800">
            ← Back to Tasks
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <!-- Status & Category -->
                <div class="flex items-center gap-2 mb-4">
                    <span class="px-2 py-1 text-xs font-medium rounded 
                        @switch($task->status)
                            @case('pending_funding') bg-yellow-100 text-yellow-800 @break
                            @case('active') bg-green-100 text-green-800 @break
                            @case('paused') bg-gray-100 text-gray-800 @break
                            @case('completed') bg-blue-100 text-blue-800 @break
                            @case('cancelled') bg-red-100 text-red-800 @break
                        @endswitch">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                    <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                        {{ ucfirst($task->category) }}
                    </span>
                </div>

                <h1 class="text-2xl font-bold mb-4">{{ $task->title }}</h1>
                
                <div class="prose max-w-none mb-6">
                    <h3 class="text-lg font-semibold mb-2">Description</h3>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $task->description }}</p>
                </div>

                @if($task->proof_instructions)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-2">Proof Instructions</h3>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $task->proof_instructions }}</p>
                </div>
                @endif

                <!-- Task Stats -->
                <div class="grid grid-cols-3 gap-4 pt-6 border-t">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $task->workers_accepted_count }}</div>
                        <div class="text-sm text-gray-500">Accepted</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-600">{{ $task->max_workers }}</div>
                        <div class="text-sm text-gray-500">Max Workers</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">₦{{ number_format($task->reward_per_user, 2) }}</div>
                        <div class="text-sm text-gray-500">Reward</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Reward Card -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 mb-1">₦{{ number_format($task->reward_per_user, 2) }}</div>
                    <div class="text-sm text-gray-500">per successful submission</div>
                </div>

                @if($task->status === 'active' && !$userHasSubmitted)
                    <button id="submit-work-btn" class="w-full mt-4 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700">
                        Submit Work
                    </button>
                @elseif($userHasSubmitted)
                    <div class="w-full mt-4 bg-gray-100 text-gray-600 py-3 rounded-lg text-center">
                        Already Submitted
                    </div>
                @elseif($task->status !== 'active')
                    <div class="w-full mt-4 bg-gray-100 text-gray-600 py-3 rounded-lg text-center">
                        Not Accepting Submissions
                    </div>
                @endif
            </div>

            <!-- Owner Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold mb-2">Posted by</h3>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-600 font-semibold">{{ substr($task->creator->name ?? 'U', 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="font-medium">{{ $task->creator->name ?? 'Unknown' }}</div>
                        <div class="text-sm text-gray-500">Posted {{ $task->created_at->diffForHumans() }}</div>
                    </div>
                </div>

                @if($task->user_id === Auth::id())
                    <div class="mt-4 pt-4 border-t">
                        <a href="{{ route('new-tasks.submissions', $task->id) }}" class="block text-center text-blue-600 hover:text-blue-800">
                            View Submissions →
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Submit Work Modal -->
    @if($task->status === 'active' && !$userHasSubmitted)
    <div id="submit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4">
            <h2 class="text-xl font-bold mb-4">Submit Work</h2>
            <form id="submit-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Proof Data (JSON)</label>
                    <textarea name="proof_data" rows="4" class="w-full border rounded-lg p-3" required placeholder='{"screenshot": "url", "link": "url"}'></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Notes (optional)</label>
                    <textarea name="notes" rows="2" class="w-full border rounded-lg p-3" placeholder="Any additional notes..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" id="cancel-submit" class="flex-1 bg-gray-200 text-gray-800 py-2 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        id="new-task-show-config"
        class="hidden"
        data-submit-url="{{ route('new-tasks.submit', $task->id) }}"
    ></div>

    <script>
        const taskShowConfig = document.getElementById('new-task-show-config');
        const submitUrl = taskShowConfig?.dataset.submitUrl || '';
        const submitBtn = document.getElementById('submit-work-btn');
        const modal = document.getElementById('submit-modal');
        const cancelBtn = document.getElementById('cancel-submit');
        const form = document.getElementById('submit-form');

        submitBtn?.addEventListener('click', () => modal.classList.remove('hidden'));
        cancelBtn?.addEventListener('click', () => modal.classList.add('hidden'));
        modal?.addEventListener('click', (e) => { if(e.target === modal) modal.classList.add('hidden'); });

        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const proofData = formData.get('proof_data');

            let parsedProof = {};
            try {
                parsedProof = JSON.parse(proofData || '{}');
            } catch (parseError) {
                if (window.SwiftkudiFormFeedback && form) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, {
                        message: 'Proof data must be valid JSON. Example: {"screenshot":"https://..."}',
                    }, {
                        boxId: 'new-task-submit-error-box',
                    });
                } else {
                    alert('Please enter valid JSON for proof data');
                }
                return;
            }
            
            try {
                const response = await fetch(submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        proof_data: parsedProof,
                        notes: formData.get('notes')
                    })
                });
                
                const data = await response.json();
                if(data.success) {
                    alert('Work submitted successfully!');
                    window.location.reload();
                } else {
                    if ((response.status === 422 || data.errors || data.error_list) && window.SwiftkudiFormFeedback && form) {
                        window.SwiftkudiFormFeedback.showValidationErrors(form, data, {
                            boxId: 'new-task-submit-error-box',
                        });
                    } else {
                        alert(data.message || 'Failed to submit work');
                    }
                }
            } catch(err) {
                if (window.SwiftkudiFormFeedback && form) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, {
                        message: 'An error occurred while submitting your work. Please try again.',
                    }, {
                        boxId: 'new-task-submit-error-box',
                    });
                } else {
                    alert('An error occurred while submitting your work. Please try again.');
                }
            }
        });
    </script>
    @endif
</div>
@endsection
