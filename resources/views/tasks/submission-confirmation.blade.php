@extends('layouts.app')

@section('title', 'Submission Received - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-dark-700">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 mt-1">
                    <i class="fas fa-check-circle text-4xl text-emerald-500"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Submission received</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Thanks — your work has been submitted and is now pending review by the task owner.</p>

                    <div class="mt-4 bg-gray-50 dark:bg-dark-800 p-4 rounded-lg border">
                        <div class="text-sm text-gray-500">Task</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $task->title }}</div>
                        <div class="text-xs text-gray-500 mt-1">Submitted: {{ $completion->created_at->format('M d, Y h:i A') }} • Status: <span class="font-semibold">{{ ucfirst($completion->status) }}</span></div>
                        <div class="mt-3 text-sm text-gray-700 dark:text-gray-300">Reward: <span class="font-bold text-green-600">₦{{ number_format($completion->reward_amount ?? 0, 2) }}</span></div>

                        @if($completion->proof_description)
                        <div class="mt-3">
                            <div class="text-sm text-gray-500">Notes from you</div>
                            <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $completion->proof_description }}</div>
                        </div>
                        @endif

                        @php
                            // show uploaded proof if present (supports legacy proof_screenshot or proof_data)
                            $rawProof = $completion->proof_data ?? $completion->proof_screenshot ?? null;
                            $proofData = null;
                            if ($rawProof) {
                                $proofData = is_string($rawProof) ? json_decode($rawProof, true) : $rawProof;
                            }
                        @endphp

                        @if($proofData && is_array($proofData) && isset($proofData['file']))
                        <div class="mt-4">
                            <div class="text-sm text-gray-500">Uploaded file</div>
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $proofData['file']) }}" alt="Proof" class="max-w-full rounded-lg border border-gray-200" />
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mt-6 flex space-x-3">
                        <a href="{{ route('tasks.show', $task) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md">View Task</a>
                        <a href="{{ route('tasks.my-tasks') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-dark-800 text-gray-800 dark:text-gray-100 rounded-md">My Tasks</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
