@extends('layouts.app')

@section('title', 'Review Submission - SwiftKudi')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <a href="{{ route('tasks.analytics', $task) }}" class="text-indigo-400 hover:text-indigo-300 mb-4 inline-block transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Task Analytics
        </a>

        <!-- Header -->
        <div class="bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-2">Review Submission</h1>
                    <p class="text-gray-400">Task: {{ $task->title }}</p>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold 
                    {{ $completion->status === 'approved' ? 'bg-emerald-900/50 text-emerald-300 border border-emerald-700' : 
                       ($completion->status === 'rejected' ? 'bg-red-900/50 text-red-300 border border-red-700' : 'bg-amber-900/50 text-amber-300 border border-amber-700') }}">
                    <i class="fas fa-{{ $completion->status === 'approved' ? 'check-circle' : ($completion->status === 'rejected' ? 'times-circle' : 'clock') }} mr-1"></i>
                    {{ ucfirst($completion->status) }}
                </span>
            </div>
        </div>

        <!-- Submission Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Worker Info -->
            <div class="bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-700">
                <h2 class="text-lg font-bold text-white mb-4"><i class="fas fa-user mr-2"></i>Worker Information</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Username:</span>
                        <span class="text-white font-medium">{{ $completion->user->username }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Email:</span>
                        <span class="text-white">{{ $completion->user->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Member Since:</span>
                        <span class="text-white">{{ $completion->user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Level:</span>
                        <span class="text-white">{{ $completion->user->level ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Tasks Completed:</span>
                        <span class="text-white">{{ $completion->user->task_completions()->approved()->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Submission Meta -->
            <div class="bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-700">
                <h2 class="text-lg font-bold text-white mb-4"><i class="fas fa-info-circle mr-2"></i>Submission Details</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Submitted:</span>
                        <span class="text-white">{{ $completion->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    @if($completion->submitted_at)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Time Since:</span>
                        <span class="text-white">{{ $completion->submitted_at->diffForHumans() }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-400">Reward:</span>
                        <span class="text-emerald-400 font-bold">₦{{ number_format($completion->reward_amount ?? $task->worker_reward_per_task, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">XP Earned:</span>
                        <span class="text-amber-400 font-bold">+{{ $completion->xp_earned ?? 0 }} XP</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proof Description -->
        <div class="bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-700">
            <h2 class="text-lg font-bold text-white mb-4"><i class="fas fa-align-left mr-2"></i>Proof Description</h2>
            <div class="bg-gray-700/50 rounded-lg p-4">
                <p class="text-gray-300 whitespace-pre-wrap">{{ $completion->proof_description ?? 'No description provided' }}</p>
            </div>
        </div>

        <!-- Proof Screenshot/Data -->
        @php
            use Illuminate\Support\Str;
            // Prefer the newer 'proof_data' column, fall back to legacy 'proof_screenshot'
            $rawProof = $completion->proof_data ?? $completion->proof_screenshot ?? null;
            $proofData = null;
            if ($rawProof) {
                $proofData = is_string($rawProof) ? json_decode($rawProof, true) : $rawProof;
            }
        @endphp

        @if($proofData)
        <div class="bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-700">
            <h2 class="text-lg font-bold text-white mb-4"><i class="fas fa-image mr-2"></i>Proof Evidence</h2>
            @if(is_array($proofData))
                @foreach($proofData as $key => $value)
                    @if($key === 'link')
                    <div class="mb-4">
                        <span class="text-gray-400 text-sm">Submitted Link:</span>
                        <a href="{{ $value }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 block mt-1 break-all">{{ $value }}</a>
                    </div>
                    @elseif($key === 'file' && $value)
                    <div class="mb-4">
                        <span class="text-gray-400 text-sm">Uploaded File:</span>
                        <div class="mt-2">
                            @if(Str::endsWith($value, ['.jpg', '.jpeg', '.png', '.gif']))
                                <img src="{{ asset('storage/' . $value) }}" alt="Proof Screenshot" class="max-w-full rounded-lg border border-gray-600">
                            @elseif(Str::endsWith($value, ['.mp4', '.mov', '.avi']))
                                <video controls class="max-w-full rounded-lg border border-gray-600">
                                    <source src="{{ asset('storage/' . $value) }}" type="video/mp4">
                                    Your browser does not support video playback.
                                </video>
                            @else
                                <a href="{{ asset('storage/' . $value) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                    <i class="fas fa-download mr-2"></i> Download File
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif
                @endforeach
            @else
                <p class="text-gray-400">Proof data: {{ is_string($rawProof) ? $rawProof : json_encode($rawProof) }}</p>
            @endif
        </div>
        @endif

        <!-- Admin Notes -->
        @if($completion->admin_notes)
        <div class="bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-700">
            <h2 class="text-lg font-bold text-white mb-4"><i class="fas fa-sticky-note mr-2"></i>Admin Notes</h2>
            <div class="bg-gray-700/50 rounded-lg p-4">
                <p class="text-gray-300">{{ $completion->admin_notes }}</p>
            </div>
        </div>
        @endif

        <!-- Rejection Reason -->
        @if($completion->rejection_reason)
        <div class="bg-red-900/20 border border-red-700 rounded-xl p-6 mb-6">
            <h2 class="text-lg font-bold text-red-400 mb-4"><i class="fas fa-exclamation-triangle mr-2"></i>Rejection Reason</h2>
            <div class="bg-red-900/30 rounded-lg p-4">
                <p class="text-red-300">{{ $completion->getRejectionReasonLabelAttribute() }}</p>
                @if($completion->admin_notes)
                <p class="text-red-400 text-sm mt-2">{{ $completion->admin_notes }}</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Action Form -->
        @if($completion->isPending())
        <div class="bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-700">
            <h2 class="text-lg font-bold text-white mb-4"><i class="fas fa-cogs mr-2"></i>Review Actions</h2>
            
            <form action="{{ route('tasks.submission.approve', $completion) }}" method="POST" class="mb-4">
                @csrf
                <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-xl font-bold hover:from-emerald-700 hover:to-green-700 transition shadow-lg">
                    <i class="fas fa-check-circle mr-2"></i> Approve Submission
                </button>
            </form>

            <form action="{{ route('tasks.submission.reject', $completion) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-300 mb-2">Rejection Reason</label>
                    <select name="reason" id="reason" required
                        class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-white">
                        <option value="">Select a reason...</option>
                        @foreach(App\Models\TaskCompletion::REJECTION_REASONS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-300 mb-2">Additional Notes (optional)</label>
                    <textarea name="notes" id="notes" rows="3"
                        class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-white placeholder-gray-400"
                        placeholder="Provide additional feedback to the worker..."></textarea>
                </div>
                <button type="submit" class="w-full py-4 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-xl font-bold hover:from-red-700 hover:to-pink-700 transition shadow-lg">
                    <i class="fas fa-times-circle mr-2"></i> Reject Submission
                </button>
            </form>
        </div>
        @elseif($completion->isApproved())
        <div class="bg-emerald-900/20 border border-emerald-700 rounded-xl p-6">
            <div class="flex items-center justify-center">
                <i class="fas fa-check-circle text-emerald-400 text-4xl mr-3"></i>
                <div>
                    <h3 class="text-xl font-bold text-emerald-400">This submission has been approved</h3>
                    <p class="text-gray-400">Worker received ₦{{ number_format($completion->reward_amount, 2) }} and {{ $completion->xp_earned }} XP</p>
                </div>
            </div>
        </div>
        @elseif($completion->isRejected())
        <div class="bg-red-900/20 border border-red-700 rounded-xl p-6">
            <div class="flex items-center justify-center">
                <i class="fas fa-times-circle text-red-400 text-4xl mr-3"></i>
                <div>
                    <h3 class="text-xl font-bold text-red-400">This submission has been rejected</h3>
                    <p class="text-gray-400">Reason: {{ $completion->getRejectionReasonLabelAttribute() }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
