@extends('layouts.app')

@section('title', 'Create Task - SwiftKudi')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-dark-950 py-8">
    <div class="max-w-3xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create a New Task</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Set up your campaign and start receiving submissions</p>
        </div>

        <!-- Progress Steps -->
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold" id="step1-indicator">1</div>
                <div class="w-16 h-1 bg-gray-300 dark:bg-gray-700" id="step1-line"></div>
                <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-700 text-gray-600 flex items-center justify-center font-bold" id="step2-indicator">2</div>
                <div class="w-16 h-1 bg-gray-300 dark:bg-gray-700" id="step2-line"></div>
                <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-700 text-gray-600 flex items-center justify-center font-bold" id="step3-indicator">3</div>
            </div>
        </div>

        <!-- Step 1: Task Details -->
        <div id="step-1" class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold mb-4">Step 1: Task Details</h2>
            
            <form id="task-form">
                @csrf
                
                <!-- Category -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                    <select name="category" id="category" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-dark-800" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Title -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Task Title</label>
                    <input type="text" name="title" id="title" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-dark-800" placeholder="e.g., Get 100 likes on my Instagram post" required minlength="5">
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea name="description" id="description" rows="4" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-dark-800" placeholder="Describe what you need..." required minlength="20"></textarea>
                </div>

                <!-- Proof Instructions -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Proof Instructions (Optional)</label>
                    <textarea name="proof_instructions" id="proof_instructions" rows="3" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-dark-800" placeholder="How should workers prove completion?"></textarea>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg">
                    Continue to Pricing
                </button>
            </form>
        </div>

        <!-- Step 2: Pricing -->
        <div id="step-2" class="hidden bg-white dark:bg-dark-900 rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold mb-4">Step 2: Pricing</h2>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reward Per Worker (₦)</label>
                <input type="number" name="reward_per_user" id="reward_per_user" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-dark-800" placeholder="Minimum ₦10" min="10" required>
                <p class="text-sm text-gray-500 mt-1">Amount each worker receives for completing the task</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Number of Workers</label>
                <input type="number" name="max_workers" id="max_workers" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-dark-800" placeholder="How many workers?" min="1" max="10000" required>
            </div>

            <!-- Budget Calculator -->
            <div class="bg-gray-100 dark:bg-dark-800 p-4 rounded-lg mb-4">
                <div class="flex justify-between mb-2">
                    <span>Reward per worker:</span>
                    <span id="display-reward">₦0</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span>× Workers:</span>
                    <span id="display-workers">0</span>
                </div>
                <hr class="my-2 border-gray-300">
                <div class="flex justify-between font-bold text-lg">
                    <span>Total Budget Required:</span>
                    <span id="display-total" class="text-indigo-600">₦0</span>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expiry (Optional)</label>
                <input type="datetime-local" name="expires_at" id="expires_at" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-dark-800">
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="goToStep(1)" class="flex-1 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold py-3 rounded-lg">
                    Back
                </button>
                <button type="button" onclick="goToStep(3)" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg">
                    Review & Fund
                </button>
            </div>
        </div>

        <!-- Step 3: Review & Fund -->
        <div id="step-3" class="hidden bg-white dark:bg-dark-900 rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold mb-4">Step 3: Review & Fund</h2>
            
            <!-- Task Summary -->
            <div class="bg-gray-100 dark:bg-dark-800 p-4 rounded-lg mb-4">
                <h3 class="font-bold mb-2" id="summary-title"></h3>
                <p class="text-sm text-gray-600 dark:text-gray-400" id="summary-description"></p>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    <div><span class="text-gray-500">Category:</span> <span id="summary-category"></span></div>
                    <div><span class="text-gray-500">Reward/Worker:</span> <span id="summary-reward"></span></div>
                    <div><span class="text-gray-500">Workers:</span> <span id="summary-workers"></span></div>
                    <div><span class="text-gray-500">Total:</span> <span id="summary-total" class="font-bold text-indigo-600"></span></div>
                </div>
            </div>

            <!-- Wallet Balance -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-4 rounded-lg mb-4">
                <div class="flex justify-between items-center">
                    <span class="font-medium">Your Available Balance:</span>
                    <span class="text-xl font-bold" id="wallet-balance">₦0</span>
                </div>
                <p class="text-sm text-gray-600 mt-1" id="balance-message"></p>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="goToStep(2)" class="flex-1 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold py-3 rounded-lg">
                    Back
                </button>
                <button type="button" onclick="createAndFundTask()" id="fund-btn" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg">
                    Fund & Publish
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentStep = 1;
let taskData = {};

document.addEventListener('DOMContentLoaded', function() {
    // Calculate budget on input change
    const rewardInput = document.getElementById('reward_per_user');
    const workersInput = document.getElementById('max_workers');
    
    function calculateTotal() {
        const reward = parseFloat(rewardInput.value) || 0;
        const workers = parseInt(workersInput.value) || 0;
        const total = reward * workers;
        
        document.getElementById('display-reward').textContent = '₦' + reward.toLocaleString();
        document.getElementById('display-workers').textContent = workers;
        document.getElementById('display-total').textContent = '₦' + total.toLocaleString();
        
        return total;
    }
    
    rewardInput.addEventListener('input', calculateTotal);
    workersInput.addEventListener('input', calculateTotal);
    
    // Load wallet balance
    loadWalletBalance();
    
    // Form submission - Step 1
    document.getElementById('task-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const category = document.getElementById('category').value;
        const title = document.getElementById('title').value;
        const description = document.getElementById('description').value;
        const proof_instructions = document.getElementById('proof_instructions').value;
        
        if (!category || !title || !description) {
            alert('Please fill in all required fields');
            return;
        }
        
        taskData = {
            category,
            title,
            description,
            proof_instructions
        };
        
        goToStep(2);
    });
});

function goToStep(step) {
    // Validate current step before moving
    if (step > currentStep) {
        if (currentStep === 1) {
            // Validate step 1
            const category = document.getElementById('category').value;
            const title = document.getElementById('title').value;
            const description = document.getElementById('description').value;
            
            if (!category || !title || !description) {
                alert('Please fill in all required fields');
                return;
            }
        } else if (currentStep === 2) {
            // Validate step 2
            const reward = parseFloat(document.getElementById('reward_per_user').value);
            const workers = parseInt(document.getElementById('max_workers').value);
            
            if (!reward || reward < 10 || !workers || workers < 1) {
                alert('Please enter valid reward (min ₦10) and workers (min 1)');
                return;
            }
            
            taskData.reward_per_user = reward;
            taskData.max_workers = workers;
            taskData.expires_at = document.getElementById('expires_at').value;
            
            // Update summary
            updateSummary();
        }
    }
    
    // Hide all steps
    document.getElementById('step-1').classList.add('hidden');
    document.getElementById('step-2').classList.add('hidden');
    document.getElementById('step-3').classList.add('hidden');
    
    // Show target step
    document.getElementById('step-' + step).classList.remove('hidden');
    
    // Update progress indicators
    updateProgress(step);
    
    currentStep = step;
}

function updateProgress(step) {
    const colors = {
        1: { active: 'bg-indigo-600 text-white', inactive: 'bg-gray-300 text-gray-600' },
        2: { active: 'bg-indigo-600 text-white', inactive: 'bg-gray-300 text-gray-600' },
        3: { active: 'bg-green-600 text-white', inactive: 'bg-gray-300 text-gray-600' }
    };
    
    // Step 1 indicator
    document.getElementById('step1-indicator').className = 'w-8 h-8 rounded-full flex items-center justify-center font-bold ' + (step >= 1 ? colors[1].active : colors[1].inactive);
    document.getElementById('step1-line').className = 'w-16 h-1 ' + (step > 1 ? 'bg-indigo-600' : 'bg-gray-300');
    
    // Step 2 indicator
    document.getElementById('step2-indicator').className = 'w-8 h-8 rounded-full flex items-center justify-center font-bold ' + (step >= 2 ? colors[2].active : colors[2].inactive);
    document.getElementById('step2-line').className = 'w-16 h-1 ' + (step > 2 ? 'bg-indigo-600' : 'bg-gray-300');
    
    // Step 3 indicator
    document.getElementById('step3-indicator').className = 'w-8 h-8 rounded-full flex items-center justify-center font-bold ' + (step >= 3 ? colors[3].active : colors[3].inactive);
}

function updateSummary() {
    const categories = { micro: 'Micro Tasks', ugc: 'UGC Tasks', growth: 'Growth Tasks', premium: 'Premium Tasks' };
    
    document.getElementById('summary-title').textContent = taskData.title;
    document.getElementById('summary-description').textContent = taskData.description.substring(0, 150) + '...';
    document.getElementById('summary-category').textContent = categories[taskData.category] || taskData.category;
    document.getElementById('summary-reward').textContent = '₦' + taskData.reward_per_user.toLocaleString();
    document.getElementById('summary-workers').textContent = taskData.max_workers;
    document.getElementById('summary-total').textContent = '₦' + (taskData.reward_per_user * taskData.max_workers).toLocaleString();
}

function loadWalletBalance() {
    fetch('{{ route("wallet.balance") }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const balance = data.data.total;
            document.getElementById('wallet-balance').textContent = '₦' + balance.toLocaleString();
            
            const required = taskData.reward_per_user * taskData.max_workers || 0;
            if (required > 0) {
                if (balance >= required) {
                    document.getElementById('balance-message').textContent = '✓ You have enough balance to fund this task';
                    document.getElementById('balance-message').className = 'text-sm text-green-600 mt-1';
                } else {
                    const needed = required - balance;
                    document.getElementById('balance-message').textContent = '⚠ You need ₦' + needed.toLocaleString() + ' more to fund this task';
                    document.getElementById('balance-message').className = 'text-sm text-red-600 mt-1';
                }
            }
        }
    })
    .catch(err => console.error('Failed to load balance'));
}

function createAndFundTask() {
    const btn = document.getElementById('fund-btn');
    btn.disabled = true;
    btn.textContent = 'Processing...';
    const form = document.getElementById('task-form') || document.querySelector('form');
    
    // First create the task
    fetch('{{ route("tasks.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(taskData)
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            if ((data.errors || data.error_list) && window.SwiftkudiFormFeedback && form) {
                window.SwiftkudiFormFeedback.showValidationErrors(form, data, {
                    boxId: 'new-task-create-error-box',
                });
            } else {
                alert(data.message || 'We could not create your task. Please review the form and try again.');
            }
            btn.disabled = false;
            btn.textContent = 'Fund & Publish';
            return;
        }
        
        // Task created, now fund it
        return fetch('{{ route("tasks.fund") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    })
    .then(res => res ? res.json() : null)
    .then(data => {
        if (data) {
            if (data.success) {
                alert('🎉 Task created and funded successfully!');
                window.location.href = '{{ route("tasks.my-tasks") }}';
            } else {
                if ((data.errors || data.error_list) && window.SwiftkudiFormFeedback && form) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, data, {
                        boxId: 'new-task-create-error-box',
                    });
                } else {
                    alert(data.message || 'Task was created but could not be funded. Please check your balance and try again.');
                }
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            }
        }
    })
    .catch(err => {
        console.error(err);
        if (window.SwiftkudiFormFeedback && form) {
            window.SwiftkudiFormFeedback.showValidationErrors(form, {
                message: 'An unexpected error occurred while processing your request. Please try again.',
            }, {
                boxId: 'new-task-create-error-box',
            });
        } else {
            alert('An error occurred');
        }
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Fund & Publish';
    });
}
</script>
@endpush
@endsection
