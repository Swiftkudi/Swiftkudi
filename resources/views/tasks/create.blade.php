@extends('layouts.app')

@section('title', 'Create Task - SwiftKudi')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-dark-950">
    <!-- Page Header -->
    <div class="bg-white dark:bg-dark-900 border-b border-gray-200 dark:border-dark-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create a Task</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Get engagement on your social media posts</p>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <form action="{{ route('tasks.create.store') }}" method="POST" class="bg-white dark:bg-dark-900 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
            @csrf
            
            <!-- Idempotency Token -->
            <input type="hidden" name="idempotency_token" value="{{ $idempotencyToken ?? '' }}">
            <!-- Hidden task_type field (set by JavaScript based on task_group) -->
            <input type="hidden" name="task_type" id="hidden_task_type" value="">
            <!-- Hidden proof_instructions field (set by JavaScript based on instructions) -->
            <input type="hidden" name="proof_instructions" id="hidden_proof_instructions" value="">

            <!-- Flash messages -->
            @if(session('success'))
                <div class="m-6 mb-0 p-4 rounded-lg bg-green-50 text-green-800 border border-green-200">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle mt-0.5 mr-3 flex-shrink-0"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="m-6 mb-0 p-4 rounded-lg bg-red-50 text-red-800 border border-red-200">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle mt-0.5 mr-3 flex-shrink-0"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                </div>
            @endif

            @if(!empty($resumeMessage))
                <div class="m-6 mb-0 p-4 rounded-lg {{ !empty($canProceed) ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                    <div class="flex items-start">
                        <i class="fas {{ !empty($canProceed) ? 'fa-check-circle' : 'fa-exclamation-circle' }} mt-0.5 mr-3 flex-shrink-0"></i>
                        <div>{{ $resumeMessage }}</div>
                    </div>
                </div>
            @endif

            @if($errors && $errors->any())
                <div class="m-6 mb-0 p-4 rounded-lg bg-yellow-50 text-yellow-800">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Wallet Balance Info -->
            @php
                $wallet = Auth::user()->wallet;
                $totalBalance = $wallet ? $wallet->withdrawable_balance + $wallet->promo_credit_balance : 0;
                $hasFormData = (bool) old('title') || !empty($prefillData['title']);

                $getValue = function($field, $default = '') {
                    if (old($field)) return old($field);
                    if (!empty($prefillData[$field])) return $prefillData[$field];
                    if (!empty($draftData[$field])) return $draftData[$field];
                    return $default;
                };
            @endphp
            
            @if(session('success') && $hasFormData)
            <div class="m-6 mb-0 p-4 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 text-blue-800 dark:text-blue-300">
                <div class="flex items-start">
                    <i class="fas fa-info-circle mt-0.5 mr-3 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold">✨ Your form is pre-filled and ready!</p>
                        <p class="text-sm mt-1">Your wallet now has sufficient balance (₦{{ number_format($totalBalance, 2) }}). Simply review the details below and click "Create Task" to complete.</p>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Balance Card -->
            <div class="m-6 mb-0 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl p-5 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-100">Wallet Balance</p>
                        <p class="text-2xl font-bold">₦{{ number_format($totalBalance, 2) }}</p>
                    </div>
                    @if($totalBalance < 2500)
                    <button type="button" id="add-funds-btn" class="px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white rounded-lg font-medium text-sm transition-colors">
                        <i class="fas fa-plus-circle mr-1"></i> Add Funds
                    </button>
                    @endif
                </div>
            </div>

            <!-- Form Content -->
            <div class="p-6 space-y-8">
                <!-- Task Title -->
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Task Title *</label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Get 100 likes on my post"
                        class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all"
                        value="{{ $getValue('title') }}">
                    @error('title')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                    <!-- Quick Title Options -->
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" onclick="setTitle('Get likes on my post')" class="px-3 py-1.5 bg-gray-100 dark:bg-dark-700 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-400 transition-all">Get likes</button>
                        <button type="button" onclick="setTitle('Get comments on my post')" class="px-3 py-1.5 bg-gray-100 dark:bg-dark-700 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-400 transition-all">Get comments</button>
                        <button type="button" onclick="setTitle('Get followers')" class="px-3 py-1.5 bg-gray-100 dark:bg-dark-700 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-400 transition-all">Get followers</button>
                        <button type="button" onclick="setTitle('Get shares')" class="px-3 py-1.5 bg-gray-100 dark:bg-dark-700 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-400 transition-all">Get shares</button>
                        <button type="button" onclick="setTitle('Join my channel')" class="px-3 py-1.5 bg-gray-100 dark:bg-dark-700 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-400 transition-all">Join channel</button>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Description *</label>
                    <textarea id="description" name="description" rows="3" required placeholder="Describe what you want workers to do..."
                        class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all resize-none">{{ $getValue('description') }}</textarea>
                </div>

                <!-- Multi-Level Category Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Task Category *</label>
                    
                    <!-- Hidden category ID field -->
                    <input type="hidden" id="category_id" name="category_id" value="{{ $getValue('category_id') }}">
                    <input type="hidden" id="platform" name="platform" value="{{ $getValue('platform') }}">
                    
                    <!-- Step 1: Task Type Group -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- Micro Tasks -->
                        <label class="task-type-option cursor-pointer">
                            <input type="radio" name="task_group" value="micro" class="task-group-input sr-only" onchange="selectTaskGroup(this.value)">
                            <div class="task-type-card bg-gray-50 dark:bg-dark-800 border-2 border-gray-200 dark:border-dark-600 rounded-xl p-4 hover:border-indigo-400 dark:hover:border-indigo-500 transition-all text-center">
                                <div class="text-3xl mb-2">⚡</div>
                                <div class="font-semibold text-gray-900 dark:text-gray-100">Micro Tasks</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Likes, Comments</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 font-semibold">₦30 - ₦250</div>
                            </div>
                        </label>
                        
                        <!-- UGC Tasks -->
                        <label class="task-type-option cursor-pointer">
                            <input type="radio" name="task_group" value="ugc" class="task-group-input sr-only" onchange="selectTaskGroup(this.value)">
                            <div class="task-type-card bg-gray-50 dark:bg-dark-800 border-2 border-gray-200 dark:border-dark-600 rounded-xl p-4 hover:border-indigo-400 dark:hover:border-indigo-500 transition-all text-center">
                                <div class="text-3xl mb-2">🎬</div>
                                <div class="font-semibold text-gray-900 dark:text-gray-100">UGC / Content</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Videos, Reviews</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 font-semibold">₦2.5k - ₦5k</div>
                            </div>
                        </label>
                        
                        <!-- Growth Tasks -->
                        <label class="task-type-option cursor-pointer">
                            <input type="radio" name="task_group" value="referral" class="task-group-input sr-only" onchange="selectTaskGroup(this.value)">
                            <div class="task-type-card bg-gray-50 dark:bg-dark-800 border-2 border-gray-200 dark:border-dark-600 rounded-xl p-4 hover:border-indigo-400 dark:hover:border-indigo-500 transition-all text-center">
                                <div class="text-3xl mb-2">📈</div>
                                <div class="font-semibold text-gray-900 dark:text-gray-100">Growth Tasks</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Invites, Joins</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 font-semibold">₦100 - ₦150</div>
                            </div>
                        </label>
                        
                        <!-- Premium Tasks -->
                        <label class="task-type-option cursor-pointer">
                            <input type="radio" name="task_group" value="premium" class="task-group-input sr-only" onchange="selectTaskGroup(this.value)">
                            <div class="task-type-card bg-gray-50 dark:bg-dark-800 border-2 border-gray-200 dark:border-dark-600 rounded-xl p-4 hover:border-indigo-400 dark:hover:border-indigo-500 transition-all text-center">
                                <div class="text-3xl mb-2">⭐</div>
                                <div class="font-semibold text-gray-900 dark:text-gray-100">Premium</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Level 2+ Only</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 font-semibold">₦500+</div>
                            </div>
                        </label>
                    </div>

                    <!-- Error message for task group -->
                    <p id="task_group_error" class="text-red-500 text-sm mt-2 hidden">Please select a task type</p>

                    <!-- Step 2 & 3: Platform & Task Type -->
                    <div id="platform-section" class="mt-6 hidden">
                        <select id="task_type" name="task_type"
                            class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 transition-all">
                            <option value="">Select platform & task type...</option>
                        </select>
                        <p id="task_type_error" class="text-red-500 text-sm mt-2 hidden">Please select a task type</p>
                    </div>

                    <!-- Selected Category Info -->
                    <div id="category-info" class="mt-4 p-4 bg-indigo-50 dark:bg-indigo-500/10 rounded-xl hidden">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100" id="selected-category-name">Selected: --</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" id="selected-category-price">Price range: --</p>
                            </div>
                            <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-semibold" id="selected-category-badge">--</span>
                        </div>
                    </div>
                </div>

                <!-- Target URL & Account -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="target_url" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Target URL</label>
                        <input type="url" id="target_url" name="target_url" placeholder="https://instagram.com/p/..."
                        class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all"
                        value="{{ $getValue('target_url') }}">
                    </div>
                    <div>
                        <label for="target_account" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Target Account</label>
                        <input type="text" id="target_account" name="target_account" placeholder="@username"
                            class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all"
                            value="{{ $getValue('target_account') }}">
                    </div>
                </div>

                <!-- Hashtag & Instructions -->
                <div>
                    <label for="hashtag" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Required Hashtag (optional)</label>
                    <input type="text" id="hashtag" name="hashtag" placeholder="#YourHashtag"
                        class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all"
                        value="{{ $getValue('hashtag') }}">
                </div>

                <div>
                    <label for="instructions" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Specific Instructions</label>
                    <textarea id="instructions" name="instructions" rows="2" placeholder="Any specific requirements for workers..."
                         class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all resize-none">{{ $getValue('instructions') }}</textarea>
                </div>

                <!-- Budget & Quantity -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="budget" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Total Budget (₦) *</label>
                        <input type="number" id="budget" name="budget" required min="100" step="1" placeholder="1000"
                             class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all"
                             value="{{ $getValue('budget') }}">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Minimum ₦2,500 for bundles</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" onclick="setBudget(2500)" class="px-3 py-1.5 bg-green-100 dark:bg-green-500/20 hover:bg-green-200 dark:hover:bg-green-500/30 rounded-lg text-xs font-semibold text-green-700 dark:text-green-400 transition-all">₦2.5k</button>
                            <button type="button" onclick="setBudget(5000)" class="px-3 py-1.5 bg-green-100 dark:bg-green-500/20 hover:bg-green-200 dark:hover:bg-green-500/30 rounded-lg text-xs font-semibold text-green-700 dark:text-green-400 transition-all">₦5k</button>
                            <button type="button" onclick="setBudget(10000)" class="px-3 py-1.5 bg-green-100 dark:bg-green-500/20 hover:bg-green-200 dark:hover:bg-green-500/30 rounded-lg text-xs font-semibold text-green-700 dark:text-green-400 transition-all">₦10k</button>
                            <button type="button" onclick="setBudget(20000)" class="px-3 py-1.5 bg-green-100 dark:bg-green-500/20 hover:bg-green-200 dark:hover:bg-green-500/30 rounded-lg text-xs font-semibold text-green-700 dark:text-green-400 transition-all">₦20k</button>
                        </div>
                    </div>
                    <div>
                        <label for="quantity" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Number of Submissions *</label>
                        <input type="number" id="quantity" name="quantity" required min="1" placeholder="20"
                             class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all"
                             value="{{ $getValue('quantity') }}">
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" onclick="setQuantity(10)" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-500/20 hover:bg-blue-200 dark:hover:bg-blue-500/30 rounded-lg text-xs font-semibold text-blue-700 dark:text-blue-400 transition-all">10</button>
                            <button type="button" onclick="setQuantity(20)" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-500/20 hover:bg-blue-200 dark:hover:bg-blue-500/30 rounded-lg text-xs font-semibold text-blue-700 dark:text-blue-400 transition-all">20</button>
                            <button type="button" onclick="setQuantity(50)" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-500/20 hover:bg-blue-200 dark:hover:bg-blue-500/30 rounded-lg text-xs font-semibold text-blue-700 dark:text-blue-400 transition-all">50</button>
                            <button type="button" onclick="setQuantity(100)" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-500/20 hover:bg-blue-200 dark:hover:bg-blue-500/30 rounded-lg text-xs font-semibold text-blue-700 dark:text-blue-400 transition-all">100</button>
                            <button type="button" onclick="setQuantity(500)" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-500/20 hover:bg-blue-200 dark:hover:bg-blue-500/30 rounded-lg text-xs font-semibold text-blue-700 dark:text-blue-400 transition-all">500</button>
                        </div>
                    </div>
                </div>

                <!-- Proof Type -->
                <div>
                    <label for="proof_type" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Proof Type *</label>
                    @php
                        $proofTypes = array_values(array_unique(\App\Models\Task::PROOF_TYPES));
                        $oldProof = old('proof_type');
                    @endphp
                    <select id="proof_type" name="proof_type" required
                         class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 transition-all">
                        <option value="">Select proof type...</option>
                        @foreach($proofTypes as $proofType)
                            <option value="{{ $proofType }}" {{ (string)$getValue('proof_type', $oldProof) === (string)$proofType ? 'selected' : '' }}>{{ ucfirst($proofType) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Requirements -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="min_followers" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Min Followers (optional)</label>
                        <input type="number" id="min_followers" name="min_followers" min="0" placeholder="0"
                             class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all"
                                value="{{ $getValue('min_followers') }}">
                    </div>
                    <div>
                        <label for="expires_at" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Expires At (optional)</label>
                        <input type="datetime-local" id="expires_at" name="expires_at"
                             class="w-full px-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 transition-all"
                                value="{{ $getValue('expires_at') }}">
                    </div>
                </div>

                <!-- Cost Preview -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white">
                    <h3 class="font-semibold text-lg mb-4">Cost Breakdown</h3>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                        <label class="text-indigo-100 flex items-center gap-3">
                            <span>Reward per task (75%):</span>
                            <input id="reward-per-task-input" name="worker_reward_per_task" type="number" min="0" step="0.01" class="w-36 px-3 py-2 rounded-lg text-indigo-900 font-bold" value="{{ $getValue('worker_reward_per_task', 0) }}">
                        </label>
                        <div class="text-sm text-indigo-100">Per-task min: <span id="min-per-task">₦0</span></div>
                    </div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-indigo-100">Platform fee (25%):</span>
                        <span class="font-bold text-lg" id="platform-fee">₦0</span>
                    </div>
                    <div class="flex justify-between items-center border-t border-indigo-400/30 pt-4 mt-4">
                        <span class="font-semibold text-lg">Total Cost:</span>
                        <span class="text-2xl font-bold" id="total-cost">₦0</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex flex-col sm:flex-row justify-end gap-4 pt-4 border-t border-gray-200 dark:border-dark-700">
                    <a href="{{ route('dashboard') }}" class="px-6 py-3.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-all text-center">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-semibold transition-all shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50">
                        Create Task
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission via AJAX to support redirect responses
    const form = document.querySelector('form[action="{{ route('tasks.create.store') }}"]');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : 'Submitting...';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }
            
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                const data = await response.json();

                // Always refresh token from backend response when provided.
                if (data && data.idempotency_token) {
                    const tokenInput = form.querySelector('input[name="idempotency_token"]');
                    if (tokenInput) {
                        tokenInput.value = data.idempotency_token;
                    }
                }

                const existingErrorBox = document.getElementById('task-create-error-box');
                if (existingErrorBox) {
                    existingErrorBox.remove();
                }

                const showValidationErrors = (payload) => {
                    const allErrors = [];
                    if (Array.isArray(payload?.error_list) && payload.error_list.length) {
                        allErrors.push(...payload.error_list);
                    } else if (payload?.errors && typeof payload.errors === 'object') {
                        Object.entries(payload.errors).forEach(([field, messages]) => {
                            const label = field.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                            if (Array.isArray(messages) && messages.length) {
                                allErrors.push(`${label}: ${messages[0]}`);
                            }
                        });
                    }

                    if (!allErrors.length) {
                        allErrors.push(payload?.message || 'Please check the form and try again.');
                    }

                    const box = document.createElement('div');
                    box.id = 'task-create-error-box';
                    box.className = 'm-6 mb-0 p-4 rounded-lg bg-red-50 text-red-800 border border-red-200';
                    box.innerHTML = `
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle mt-0.5 mr-3 flex-shrink-0"></i>
                            <div>
                                <p class="font-semibold mb-1">Please correct the following and try again:</p>
                                <ul class="list-disc pl-5">${allErrors.map(err => `<li>${err}</li>`).join('')}</ul>
                            </div>
                        </div>
                    `;

                    form.insertAdjacentElement('afterbegin', box);
                    box.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    const firstField = payload?.errors ? Object.keys(payload.errors)[0] : null;
                    if (firstField) {
                        const input = form.querySelector(`[name="${firstField}"]`);
                        if (input) {
                            input.focus();
                        }
                    }
                };
                
                if (data.success) {
                    const successMessage = data.message || 'Task created successfully.';
                    alert(successMessage);

                    // Success - redirect to my-tasks or show success
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.task_id) {
                        window.location.href = '{{ route('tasks.my-tasks') }}';
                    } else {
                        window.location.reload();
                    }
                } else {
                    // Error - check if redirect is needed (e.g., insufficient balance)
                    if (data.redirect && data.required_amount) {
                        // Redirect with required amount as query parameter
                        const redirectUrl = data.redirect + '?required=' + data.required_amount;
                        
                        // Show message and redirect
                        alert(data.message + '\n\nYou will be redirected to deposit funds.');
                        window.location.href = redirectUrl;
                        return;
                    }
                    
                    // Show error message
                    if (response.status === 422 || data.errors || data.error_list) {
                        showValidationErrors(data);
                    } else {
                        alert(data.message || 'We could not submit your task. Please review your details and try again.');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                }
            } catch (error) {
                console.error('Form submission error:', error);
                alert('An error occurred. Please try again.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            }
        });
    }
    
    const categoryConfig = @json($categoryConfig);
    const rawCategories = @json($categories->toArray());
    const platformNames = @json(\App\Models\Task::PLATFORMS);

    const categoryIdInput = document.getElementById('category_id');
    const platformInput = document.getElementById('platform');
    const budgetInput = document.getElementById('budget');
    const quantityInput = document.getElementById('quantity');
    const taskTypeSelect = document.getElementById('task_type');
    const platformSection = document.getElementById('platform-section');
    const categoryInfo = document.getElementById('category-info');

    // Track the currently selected per-task total price (reward + fee). When >0 we use it to compute budget/quantity/reward.
    let selectedTaskPriceTotal = 0;

    function ensureGroupConfig(group) {
        if (categoryConfig[group] && categoryConfig[group].platforms && categoryConfig[group].platforms.length) {
            return categoryConfig[group];
        }
        const groupObj = { label: group, badge: '', badgeText: '', minBudget: 0, platforms: [] };
        rawCategories.forEach(cat => {
            const g = cat.task_type || 'micro';
            if (g !== group) return;
            const platform = cat.platform || 'other';
            let p = groupObj.platforms.find(x => x.id === platform);
            if (!p) {
                p = { id: platform, name: platform, icon: '', tasks: [] };
                groupObj.platforms.push(p);
            }
            p.tasks.push({ value: cat.id, name: cat.name, price: { min: parseFloat(cat.min_price || cat.base_price || 0), max: parseFloat(cat.max_price || cat.base_price || 0) }, categoryName: cat.name, proof_type: cat.proof_type, min_level: parseInt(cat.min_level || 1) });
        });
        categoryConfig[group] = groupObj;
        return groupObj;
    }

    window.selectTaskGroup = function(group) {
        const groupData = ensureGroupConfig(group);
        document.querySelectorAll('.task-group-input').forEach(input => {
            const card = input.closest('.task-type-option')?.querySelector('.task-type-card');
            if (card) {
                card.classList.toggle('border-indigo-500', input.value === group);
                card.classList.toggle('bg-indigo-50', input.value === group);
                card.classList.toggle('border-gray-200', input.value !== group);
            }
        });
        document.getElementById('task_group_error')?.classList.add('hidden');
        platformSection.classList.remove('hidden');
        categoryInfo.classList.add('hidden');
        
        taskTypeSelect.innerHTML = '<option value="">Select platform & task type...</option>';
        (groupData.platforms || []).forEach((p) => {
            if (!p.tasks || !p.tasks.length) return;
            const optgroup = document.createElement('optgroup');
            optgroup.label = p.name || (platformNames[p.id] || p.id);
            p.tasks.forEach(task => {
                const option = document.createElement('option');
                option.value = task.value;
                const minP = task.price && task.price.min ? task.price.min : 0;
                const maxP = task.price && task.price.max ? task.price.max : minP;
                option.textContent = `${task.name} (₦${minP.toLocaleString()}${maxP !== minP ? ' - ₦' + maxP.toLocaleString() : ''})`;
                option.dataset.price = JSON.stringify(task.price || {});
                option.dataset.categoryName = task.categoryName || '';
                option.dataset.group = group;
                option.dataset.platform = p.id;
                optgroup.appendChild(option);
            });
            taskTypeSelect.appendChild(optgroup);
        });
        budgetInput.min = groupData.minBudget || 0;
        categoryIdInput.value = '';
        platformInput.value = '';
    };

    window.selectTaskType = function(select) {
        const option = select.options[select.selectedIndex];
        if (!option || !option.value) {
            categoryInfo.classList.add('hidden');
            categoryIdInput.value = '';
            selectedTaskPriceTotal = 0;
            if (window.__setCurrentMinPer) window.__setCurrentMinPer(0);
            updateCostPreview();
            return;
        }
        const price = option.dataset.price ? JSON.parse(option.dataset.price) : {};
        const categoryName = option.dataset.categoryName || option.text.split(' (')[0];
        const platform = option.dataset.platform;

        categoryIdInput.value = option.value;
        platformInput.value = platform;
        document.getElementById('selected-category-name').textContent = `Selected: ${categoryName}`;
        const minP = price && price.min ? parseFloat(price.min) : 0;
        const maxP = price && price.max ? parseFloat(price.max) : minP;
        document.getElementById('selected-category-price').textContent = `Price range: ₦${minP.toLocaleString()}${maxP !== minP ? ' - ₦' + maxP.toLocaleString() : ''}`;
        document.getElementById('selected-category-badge').textContent = platformNames[platform] || platform;
        categoryInfo.classList.remove('hidden');
        document.getElementById('task_type_error')?.classList.add('hidden');

        // Treat the category min price as the per-task TOTAL price (reward + platform fee)
        selectedTaskPriceTotal = minP || 0;
        if (window.__setCurrentMinPer) window.__setCurrentMinPer(selectedTaskPriceTotal);

        // If a per-task price exists, ensure reward input and budget/quantity sync to that price
        const rewardInputEl = document.getElementById('reward-per-task-input');
        const currentBudget = parseFloat(budgetInput.value) || 0;
        const currentQuantity = parseInt(quantityInput.value) || 0;

        if (selectedTaskPriceTotal > 0) {
            // set reward to 75% of per-task total
            const rewardVal = (selectedTaskPriceTotal * 0.75);
            if (rewardInputEl) rewardInputEl.value = rewardVal.toFixed(2);

            // If a budget was provided, compute quantity = floor(budget / price)
            if (currentBudget > 0) {
                const calcQty = Math.max(1, Math.floor(currentBudget / selectedTaskPriceTotal));
                quantityInput.value = calcQty;
                // ensure budget reflects exact quantity * price (avoid fractional leftovers)
                budgetInput.value = (calcQty * selectedTaskPriceTotal).toFixed(2);
            } else if (currentQuantity > 0) {
                // If quantity exists but budget doesn't, compute budget = qty * price
                budgetInput.value = (currentQuantity * selectedTaskPriceTotal).toFixed(2);
            } else {
                // suggest defaults when neither set
                quantityInput.value = Math.max(1, parseInt(quantityInput.value) || 10);
                budgetInput.value = (parseInt(quantityInput.value) * selectedTaskPriceTotal).toFixed(2);
            }

            updateCostPreview();
        }
    };

    window.setBudget = function(amount) {
        budgetInput.value = amount;
        // if a task price is selected, adjust quantity to match budget exactly
        if (selectedTaskPriceTotal > 0) {
            const qty = Math.max(1, Math.floor((parseFloat(amount) || 0) / selectedTaskPriceTotal));
            quantityInput.value = qty;
            // ensure budget matches exact qty * price to avoid fractional remainder
            budgetInput.value = (qty * selectedTaskPriceTotal).toFixed(2);
        }
        updateCostPreview();
    };

    window.setQuantity = function(amount) {
        quantityInput.value = amount;
        // if a task price is selected, set budget = qty * price
        if (selectedTaskPriceTotal > 0) {
            budgetInput.value = (Math.max(1, parseInt(amount) || 0) * selectedTaskPriceTotal).toFixed(2);
        }
        updateCostPreview();
    };

    window.setTitle = function(title) {
        document.getElementById('title').value = title;
    };

    function updateCostPreview() {
        const budget = parseFloat(budgetInput.value) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        let rewardPerTask = 0;

        // If a per-task total price is selected, reward is 75% of that total; otherwise fall back to computed reward from budget
        if (selectedTaskPriceTotal > 0) {
            rewardPerTask = selectedTaskPriceTotal * 0.75;
        } else if (quantity > 0) {
            rewardPerTask = (budget * 0.75 / quantity) || 0;
        }

        const platformFee = budget * 0.25;

        // Update UI display: reward-per-task input and display
        const rewardInputEl = document.getElementById('reward-per-task-input');
        if (rewardInputEl && selectedTaskPriceTotal > 0) {
            rewardInputEl.value = rewardPerTask.toFixed(2);
        }

        // update the visible fields
        const rewardDisplay = document.getElementById('reward-per-task');
        if (rewardDisplay) rewardDisplay.textContent = `₦${rewardPerTask.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        document.getElementById('platform-fee').textContent = `₦${platformFee.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        document.getElementById('total-cost').textContent = `₦${budget.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }

    // Update listeners: budget and quantity should recompute the other when a task price is present
    budgetInput.addEventListener('input', function(){
        if (selectedTaskPriceTotal > 0) {
            const b = parseFloat(this.value) || 0;
            const qty = Math.max(1, Math.floor(b / selectedTaskPriceTotal));
            quantityInput.value = qty;
            // set budget to exact qty * price to avoid mismatch
            this.value = (qty * selectedTaskPriceTotal).toFixed(2);
        }
        updateCostPreview();
    });

    quantityInput.addEventListener('input', function(){
        if (selectedTaskPriceTotal > 0) {
            const q = Math.max(1, parseInt(this.value) || 0);
            budgetInput.value = (q * selectedTaskPriceTotal).toFixed(2);
        }
        updateCostPreview();
    });

    const rewardInput = document.getElementById('reward-per-task-input');
    let currentMinPer = 0;

    // When reward input changes, recompute budget to match desired reward per task
    if (rewardInput) {
        rewardInput.addEventListener('input', function() {
            const reward = parseFloat(this.value) || 0;
            const quantity = parseInt(quantityInput.value) || 0;

            // If a task price is present enforce reward = 75% of selected price and adjust budget accordingly
            if (selectedTaskPriceTotal > 0) {
                const enforcedReward = (selectedTaskPriceTotal * 0.75);
                if (enforcedReward !== reward) this.value = enforcedReward.toFixed(2);
                if (quantity > 0) {
                    const budgetVal = (selectedTaskPriceTotal * quantity);
                    budgetInput.value = (Math.max(0, Math.round(budgetVal * 100) / 100)).toFixed(2);
                }
                updateCostPreview();
                return;
            }

            // enforce minimum per-task reward for selected category (if any)
            const effectiveReward = Math.max(reward, currentMinPer || 0);
            if (effectiveReward !== reward) {
                this.value = effectiveReward.toFixed(2);
            }
            if (quantity > 0) {
                // budget = (reward * quantity) / 0.75
                const budgetVal = (effectiveReward * quantity) / 0.75;
                budgetInput.value = Math.max(0, Math.round(budgetVal * 100) / 100);
            }
            updateCostPreview();
        });
    }

    taskTypeSelect.addEventListener('change', function() {
        selectTaskType(this);
        updateCostPreview();
    });
 
    updateCostPreview();
    
    // Expose function to set category min when task type is selected
    window.__setCurrentMinPer = function(min) {
        currentMinPer = min || 0;
        const minEl = document.getElementById('min-per-task');
        if (minEl) minEl.textContent = `₦${(currentMinPer || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
    };

    // expose save and deposit helper to save form to session then redirect to deposit
    async function saveFormAndGoToDeposit() {
        const btn = document.getElementById('add-funds-btn');
        if (!btn) return window.location = '{{ route('wallet.deposit') }}';
        try {
            btn.disabled = true;
            btn.textContent = 'Saving...';

            // Collect form values similar to server saveDraft fields
            const formEl = document.querySelector('form[action="{{ route('tasks.create.store') }}"]');
            const data = {};
            if (!formEl) return window.location = '{{ route('wallet.deposit') }}';
            ['title','description','platform','task_type','category_id','target_url','target_account','hashtag','instructions','proof_type','budget','quantity','min_followers','expires_at'].forEach(name => {
                const el = formEl.querySelector('[name="' + name + '"]');
                if (!el) return;
                if (el.type === 'checkbox' || el.type === 'radio') data[name] = el.checked;
                else data[name] = el.value;
            });

            const resp = await fetch('{{ route('tasks.create.save-draft') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data),
                credentials: 'same-origin'
            });

            // redirect to deposit page regardless of response (deposit controller will resume)
            window.location = '{{ route('wallet.deposit') }}';
        } catch (e) {
            console.warn('Failed to save draft before deposit:', e);
            window.location = '{{ route('wallet.deposit') }}';
        }
    }

    // Attach handler to Add Funds button
    const addFundsBtn = document.getElementById('add-funds-btn');
    if (addFundsBtn) addFundsBtn.addEventListener('click', saveFormAndGoToDeposit);

    // Initialize from any prefilled category (old input or server prefill)
    (function initializePrefill() {
        try {
            const initialCategoryId = (categoryIdInput && categoryIdInput.value) ? categoryIdInput.value : '{{ old('category_id', isset($prefillData['category_id']) ? $prefillData['category_id'] : '') }}';
            if (!initialCategoryId) return;

            // find category data from rawCategories
            const cat = rawCategories.find(c => String(c.id) === String(initialCategoryId));
            if (!cat) return;

            // ensure task type select contains this category as an option so it will submit
            try {
                const existingOpt = Array.from(taskTypeSelect.options).find(o => String(o.value) === String(cat.id));
                if (!existingOpt) {
                    const opt = document.createElement('option');
                    opt.value = cat.id;
                    const minP = parseFloat(cat.min_price || cat.base_price || 0);
                    const maxP = parseFloat(cat.max_price || cat.base_price || minP);
                    opt.textContent = `${cat.name} (₦${minP.toLocaleString()}${maxP !== minP ? ' - ₦' + maxP.toLocaleString() : ''})`;
                    opt.dataset.price = JSON.stringify({ min: minP, max: maxP });
                    opt.dataset.categoryName = cat.name || '';
                    opt.dataset.group = cat.task_type || '';
                    opt.dataset.platform = cat.platform || '';
                    taskTypeSelect.appendChild(opt);
                }
                taskTypeSelect.value = cat.id;
            } catch(e) {
                // ignore
            }

            // populate hidden fields and selected category UI
            categoryIdInput.value = cat.id;
            platformInput.value = cat.platform || '';
            document.getElementById('selected-category-name').textContent = `Selected: ${cat.name || ''}`;
            const minP = parseFloat(cat.min_price || cat.base_price || 0);
            const maxP = parseFloat(cat.max_price || cat.base_price || minP);
            document.getElementById('selected-category-price').textContent = `Price range: ₦${minP.toLocaleString()}${maxP !== minP ? ' - ₦' + maxP.toLocaleString() : ''}`;
            document.getElementById('selected-category-badge').textContent = (platformNames[cat.platform] || cat.platform) || '';
            categoryInfo.classList.remove('hidden');

            // set the selected per-task total price and compute reward/budget/quantity accordingly
            selectedTaskPriceTotal = minP || 0;
            if (selectedTaskPriceTotal > 0) {
                const rewardInputEl = document.getElementById('reward-per-task-input');
                if (rewardInputEl) rewardInputEl.value = (selectedTaskPriceTotal * 0.75).toFixed(2);

                // if budget or quantity present, sync them
                const currentBudget = parseFloat(budgetInput.value) || 0;
                const currentQuantity = parseInt(quantityInput.value) || 0;
                if (currentBudget > 0) {
                    const calcQty = Math.max(1, Math.floor(currentBudget / selectedTaskPriceTotal));
                    quantityInput.value = calcQty;
                    budgetInput.value = (calcQty * selectedTaskPriceTotal).toFixed(2);
                } else if (currentQuantity > 0) {
                    budgetInput.value = (currentQuantity * selectedTaskPriceTotal).toFixed(2);
                } else {
                    quantityInput.value = Math.max(1, parseInt(quantityInput.value) || 10);
                    budgetInput.value = (parseInt(quantityInput.value) * selectedTaskPriceTotal).toFixed(2);
                }

                updateCostPreview();
            }
        } catch (e) { /* ignore init errors */ }
    })();
});
 </script>

<script>
(function(){
    const FORM_KEY = 'task_create_draft_v1';
    const AUTOSAVE_MS = 10 * 1000; // 10s
    const KEEP_ALIVE_MS = 3 * 60 * 1000; // 3m
    const warnBeforeMs = 2 * 60 * 1000; // warn 2m before expiry

    const form = document.querySelector('form[action="{{ route('tasks.create.store') }}"]');
    if (!form) return;

    // Restore draft
    try {
        const raw = localStorage.getItem(FORM_KEY);
        if (raw) {
            const data = JSON.parse(raw);
            Object.keys(data).forEach(name => {
                const el = form.querySelector('[name="' + name + '"]');
                if (!el) return;

                // Do not overwrite values already prefixed by server/session resume data.
                if (el.type === 'checkbox' || el.type === 'radio') {
                    if (!el.checked) {
                        el.checked = data[name];
                    }
                } else {
                    const currentValue = (el.value || '').toString().trim();
                    if (currentValue === '' && typeof data[name] !== 'undefined' && data[name] !== null) {
                        el.value = data[name];
                    }
                }
            });
        }
    } catch (e) { /* ignore restore errors */ }

    // Autosave function
    function saveDraft(){
        try {
            const payload = {};
            form.querySelectorAll('input[name],textarea[name],select[name]').forEach(el => {
                if (el.type === 'password') return;
                if (el.type === 'checkbox' || el.type === 'radio') payload[el.name] = el.checked;
                else payload[el.name] = el.value;
            });
            localStorage.setItem(FORM_KEY, JSON.stringify(payload));
        } catch (e) { /* ignore save errors */ }
    }

    const autosaveTimer = setInterval(saveDraft, AUTOSAVE_MS);

    // Clear draft on successful submit
    form.addEventListener('submit', function(){
        // Populate hidden fields before submit
        const taskGroupEl = form.querySelector('input[name="task_group"]:checked');
        const taskTypeHidden = document.getElementById('hidden_task_type');
        if (taskGroupEl && taskTypeHidden) {
            // Convert task_group to task_type (e.g., 'referral' -> 'growth')
            const taskGroup = taskGroupEl.value;
            const taskTypeMap = {
                'micro': 'micro',
                'ugc': 'ugc',
                'referral': 'growth',
                'premium': 'premium'
            };
            taskTypeHidden.value = taskTypeMap[taskGroup] || taskGroup;
        }
        
        // Copy instructions to proof_instructions
        const instructionsEl = form.querySelector('[name="instructions"]');
        const proofInstructionsHidden = document.getElementById('hidden_proof_instructions');
        if (instructionsEl && proofInstructionsHidden) {
            proofInstructionsHidden.value = instructionsEl.value;
        }
        
        try { localStorage.removeItem(FORM_KEY); } catch(e){}
        clearInterval(autosaveTimer);
    });

    // Keep-alive ping to prevent session expiry (and therefore CSRF invalidation)
    async function keepAlivePing(){
        try {
            await fetch('/', { method: 'GET', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const badge = document.getElementById('csrf-refresh-badge');
            if (badge) { badge.textContent = 'session refreshed'; setTimeout(()=> badge.textContent = '', 1400); }
        } catch(e) { /* ignore network errors */ }
    }
    const keepAliveTimer = setInterval(keepAlivePing, KEEP_ALIVE_MS);
    // initial ping
    keepAlivePing();

    // Session expiry warning UI
    const sessionLifetimeMinutes = parseInt('{{ config('session.lifetime') }}',10) || 120;
    const sessionMs = sessionLifetimeMinutes * 60 * 1000;
    const warnAt = Math.max(0, sessionMs - warnBeforeMs);

    const badge = document.createElement('div');
    badge.id = 'csrf-refresh-badge';
    badge.style.cssText = 'position:fixed;right:12px;bottom:12px;z-index:60;padding:8px 12px;border-radius:10px;background:rgba(0,0,0,0.6);color:#fff;font-size:12px;backdrop-filter:blur(4px);pointer-events:none;';
    document.body.appendChild(badge);

    let warnTimer = null;
    function scheduleWarning(){
        if (warnTimer) clearTimeout(warnTimer);
        warnTimer = setTimeout(()=>{
            // show prompt to user to refresh session
            const keep = confirm('Your session will expire soon. Click OK to keep it active.');
            if (keep) {
                // user asked to keep alive
                keepAlivePing();
                scheduleWarning();
            }
        }, warnAt);
    }
    scheduleWarning();

    // Reset warning on user activity so active users aren't nagged
    const activityEvents = ['mousemove','keydown','scroll','click','touchstart'];
    const resetOnActivity = () => {
        scheduleWarning();
    };
    activityEvents.forEach(ev => window.addEventListener(ev, resetOnActivity));

})();
</script>

@endpush
