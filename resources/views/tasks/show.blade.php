@extends('layouts.app')

@section('title', $task->title . ' - SwiftKudi')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <a href="{{ route('tasks.index') }}" class="text-indigo-400 hover:text-indigo-300 mb-4 inline-block transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Tasks
        </a>

        @if(session('success'))
            <div class="mb-4 p-4 rounded-lg bg-emerald-900/20 border border-emerald-700 text-emerald-300">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 rounded-lg bg-red-900/20 border border-red-700 text-red-300">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Task Details -->
        <div class="bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                    {{ $task->platform === 'instagram' ? 'bg-pink-900/50 text-pink-300 border border-pink-700' : 
                       ($task->platform === 'twitter' ? 'bg-blue-900/50 text-blue-300 border border-blue-700' : 
                       ($task->platform === 'tiktok' ? 'bg-gray-700 text-gray-300 border border-gray-600' : 
                       ($task->platform === 'youtube' ? 'bg-red-900/50 text-red-300 border border-red-700' : 'bg-gray-700 text-gray-300 border border-gray-600'))) }}">
                    <i class="fab fa-{{ $task->platform }} mr-1"></i>
                    {{ ucfirst($task->platform) }} • {{ ucfirst($task->task_type) }}
                </span>
                @if($task->is_featured)
                <span class="text-yellow-400"><i class="fas fa-star mr-1"></i> Featured</span>
                @endif
            </div>

            <h1 class="text-2xl font-bold text-white mb-4">{{ $task->title }}</h1>
            
            <p class="text-gray-300 mb-6">{{ $task->description }}</p>

            <!-- Task Stats -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="text-center p-4 bg-gray-700/50 rounded-lg border border-gray-600">
                    <div class="text-2xl font-bold text-emerald-400">₦{{ number_format($task->worker_reward_per_task, 2) }}</div>
                    <div class="text-sm text-gray-400">Reward</div>
                </div>
                <div class="text-center p-4 bg-gray-700/50 rounded-lg border border-gray-600">
                    <div class="text-2xl font-bold text-white">{{ $task->completed_slots }}/{{ $task->quantity }}</div>
                    <div class="text-sm text-gray-400">Completed</div>
                </div>
                <div class="text-center p-4 bg-gray-700/50 rounded-lg border border-gray-600">
                    <div class="text-2xl font-bold text-amber-400">{{ $task->quantity - $task->completed_slots }}</div>
                    <div class="text-sm text-gray-400">Remaining</div>
                </div>
            </div>

            <!-- Target Info -->
            @if($task->target_url)
            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-400 mb-2">Target URL:</h3>
                <a href="{{ $task->target_url }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 break-all transition">{{ $task->target_url }}</a>
            </div>
            @endif

            @if($task->target_account)
            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-400 mb-2">Target Account:</h3>
                <p class="text-white">{{ $task->target_account }}</p>
            </div>
            @endif

            @if($task->hashtag)
            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-400 mb-2">Hashtag:</h3>
                <p class="text-white">{{ $task->hashtag }}</p>
            </div>
            @endif

            <!-- Proof Type -->
            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-400 mb-2">Proof Required:</h3>
                <span class="px-3 py-1 bg-amber-900/50 text-amber-300 rounded-full text-sm border border-amber-700">
                    <i class="fas fa-{{ $task->proof_type === 'screenshot' ? 'camera' : ($task->proof_type === 'video' ? 'video' : 'link') }} mr-1"></i>
                    {{ ucfirst($task->proof_type) }}
                </span>
            </div>
        </div>

        <!-- Submit Form or Status -->
        @if($existingSubmission)
        <div class="bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-700">
            <h2 class="text-lg font-bold text-white mb-4">Your Submission</h2>
            <div class="flex items-center justify-between p-4 bg-gray-700/50 rounded-lg border border-gray-600">
                <div>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold 
                        {{ $existingSubmission->status === 'approved' ? 'bg-emerald-900/50 text-emerald-300 border border-emerald-700' : 
                           ($existingSubmission->status === 'rejected' ? 'bg-red-900/50 text-red-300 border border-red-700' : 'bg-amber-900/50 text-amber-300 border border-amber-700') }}">
                        <i class="fas fa-{{ $existingSubmission->status === 'approved' ? 'check-circle' : ($existingSubmission->status === 'rejected' ? 'times-circle' : 'clock') }} mr-1"></i>
                        {{ ucfirst($existingSubmission->status) }}
                    </span>
                    <p class="text-sm text-gray-400 mt-2">Submitted {{ $existingSubmission->created_at->diffForHumans() }}</p>
                </div>
                @if($existingSubmission->status === 'approved')
                <div class="text-right">
                    <div class="text-xl font-bold text-emerald-400">+₦{{ number_format($existingSubmission->reward_amount, 2) }}</div>
                    <div class="text-sm text-gray-400">Earned</div>
                </div>
                @endif
            </div>
        </div>
        @elseif(!$canPerform)
        <div class="bg-amber-900/20 border border-amber-700 rounded-lg p-4">
            <p class="text-amber-400"><i class="fas fa-exclamation-triangle mr-2"></i>You cannot perform this task.</p>
        </div>
        @else
        <!-- Submission Form -->
        <div class="bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-700">
            <h2 class="text-lg font-bold text-white mb-4">Submit Your Work</h2>

            @php
                // Show friendly upload limits to users; keep a single client max value
                $serverUploadMax = ini_get('upload_max_filesize') ?: 'unknown';
                $serverPostMax = ini_get('post_max_size') ?: 'unknown';
                $clientMaxMB = 64; // visible client-side max (MB)
            @endphp

            <div class="mb-4">
                <p class="text-sm text-gray-400">Accepted: images & videos. Maximum file size: <strong>{{ $clientMaxMB }} MB</strong>. Server allows uploads up to <strong>{{ $serverUploadMax }}</strong> (post max: {{ $serverPostMax }}). If your upload fails, try a smaller file or contact support.</p>
            </div>

            <form action="{{ route('tasks.submit', $task) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-6">
                    <label for="proof_data" class="block text-sm font-medium text-gray-300 mb-2">
                        {{ $task->proof_type === 'link' ? 'Link Proof' : 'Upload Proof' }}
                        @if($task->proof_type === 'screenshot')
                            <span class="text-xs text-gray-500">(Screenshot of completed task)</span>
                        @elseif($task->proof_type === 'video')
                            <span class="text-xs text-gray-500">(Video showing completion)</span>
                        @endif
                    </label>
                    @if($task->proof_type === 'link')
                        <input type="url" name="proof_data[link]" id="proof_data" required
                            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400 transition"
                            placeholder="https://...">
                    @else
                        <div class="relative">
                            <input type="file" name="proof_data[file]" id="proof_data" accept="image/*,video/*" required
                                class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition">
                            <p class="text-xs text-gray-500 mt-2">Accepted: Images and Videos (Max: {{ $clientMaxMB }}MB)</p>
                        </div>
                    @endif
                    @error('proof_data')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="proof_description" class="block text-sm font-medium text-gray-300 mb-2">Proof Description</label>
                    <textarea name="proof_description" id="proof_description" rows="3" required
                        class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400 transition"
                        placeholder="Describe what you did to complete this task..."></textarea>
                    @error('proof_description')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-300 mb-2">Additional Notes (optional)</label>
                    <textarea name="notes" id="notes" rows="2"
                        class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400 transition"
                        placeholder="Any additional information..."></textarea>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-medium hover:from-indigo-700 hover:to-purple-700 transition shadow-lg">
                    <i class="fas fa-check-circle mr-2"></i> Submit Task
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

{{-- Client-side validation for file uploads --}}
<script>
    (function(){
        const taskShowConfig = document.getElementById('task-show-config');
        const form = document.querySelector('form[action*="/submit"]');
        if (!form) return;

        const fileInput = document.querySelector('input[type="file"][name^="proof_data"]');
        const maxMB = Number(taskShowConfig?.dataset.maxFileMb || 64); // client-side limit (MB) driven by server-side variable
        const maxBytes = maxMB * 1024 * 1024;

        function showError(msg) {
            // create or reuse an alert element
            let alert = document.getElementById('file-size-error');
            if (!alert) {
                alert = document.createElement('p');
                alert.id = 'file-size-error';
                alert.className = 'text-red-400 text-sm mt-2';
                // insert after the file input container
                if (fileInput && fileInput.parentNode) {
                    fileInput.parentNode.parentNode.appendChild(alert);
                } else {
                    form.insertBefore(alert, form.firstChild);
                }
            }
            alert.textContent = msg;
        }

        if (fileInput) {
            fileInput.addEventListener('change', function(e){
                const f = e.target.files && e.target.files[0];
                if (!f) {
                    // clear error
                    const existing = document.getElementById('file-size-error');
                    if (existing) existing.remove();
                    return;
                }
                if (f.size > maxBytes) {
                    showError(`Selected file is too large. Max allowed: ${maxMB}MB.`);
                    fileInput.value = '';
                } else {
                    const existing = document.getElementById('file-size-error');
                    if (existing) existing.remove();
                }
            });

            form.addEventListener('submit', function(e){
                const f = fileInput.files && fileInput.files[0];
                if (f && f.size > maxBytes) {
                    e.preventDefault();
                    showError(`Selected file is too large. Max allowed: ${maxMB}MB.`);
                    return false;
                }
            });
        }
    })();
</script>

<div
    id="task-show-config"
    class="hidden"
    data-max-file-mb="{{ (int) ($clientMaxMB ?? 64) }}"
></div>

@endsection
