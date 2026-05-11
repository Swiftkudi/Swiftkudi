@extends('layouts.app')

@section('title', 'Search & Import Profiles - SwiftKudi')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-dark-950">
    <!-- Page Header -->
    <div class="bg-white dark:bg-dark-900 border-b border-gray-200 dark:border-dark-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Search & Import Profiles</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Find social media profiles and create tasks in bulk</p>
                </div>
                <a href="{{ route('tasks.create.new') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-600">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Manual Create
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Search Form -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Search Profiles</h2>
                    
                    <form id="search-form">
                        <div class="space-y-4">
                            <div>
                                <label for="search-query" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Query</label>
                                <input type="text" id="search-query" name="query" placeholder="e.g., tech influencer, crypto trader" 
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                                <p class="text-xs text-gray-500 mt-1">Enter keywords, hashtags, or topics to search</p>
                            </div>

                            <div>
                                <label for="search-platform" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Platform</label>
                                <select id="search-platform" name="platform"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                                    <option value="">All Platforms</option>
                                    @foreach($platforms as $platform)
                                    <option value="{{ $platform['id'] }}">{{ $platform['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="search-limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Results</label>
                                <select id="search-limit" name="limit"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                                    <option value="10">10 profiles</option>
                                    <option value="20" selected>20 profiles</option>
                                    <option value="30">30 profiles</option>
                                    <option value="50">50 profiles</option>
                                </select>
                            </div>

                            <button type="submit" class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                        </div>
                    </form>

                    <!-- Loading State -->
                    <div id="search-loading" class="hidden text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-600 border-t-transparent"></div>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Searching profiles...</p>
                    </div>
                </div>

                <!-- Task Settings -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6 mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Task Settings</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="task-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Task Type</label>
                            <select id="task-type"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                                <option value="">Select task type...</option>
                                @foreach($microTaskTypes as $type => $config)
                                <option value="{{ $type }}">{{ $config['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="reward-per-task" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reward (₦)</label>
                                <input type="number" id="reward-per-task" value="100" min="10" step="10"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                            </div>
                            <div>
                                <label for="quantity-per-profile" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Qty/Profile</label>
                                <input type="number" id="quantity-per-profile" value="10" min="1"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label for="task-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Task Title</label>
                            <input type="text" id="task-title" value="{{ old('title', 'Get engagement on profile') }}"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                        </div>

                        <div>
                            <label for="task-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Task Description</label>
                            <textarea id="task-description" rows="3"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white resize-none">Follow the profile, like their recent posts, and leave a positive comment.</textarea>
                        </div>
                    </div>

                    <!-- Cost Preview -->
                    <div class="mt-4 p-4 bg-indigo-50 dark:bg-indigo-500/10 rounded-xl">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Selected Profiles:</span>
                            <span class="font-semibold" id="selected-count">0</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600 dark:text-gray-400">Est. Total Budget:</span>
                            <span class="font-semibold text-indigo-600" id="est-budget">₦0</span>
                        </div>
                    </div>

                    <button type="button" id="import-btn" disabled
                        class="w-full mt-4 px-4 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-download mr-2"></i>Create Tasks
                    </button>
                </div>
            </div>

            <!-- Results -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Search Results</h2>
                        <button type="button" id="select-all-btn" class="text-sm text-indigo-600 hover:text-indigo-700 disabled:opacity-50">
                            Select All
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div id="empty-state" class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-search text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No profiles found</h3>
                        <p class="text-gray-500 dark:text-gray-400">Enter a search query to find social media profiles</p>
                    </div>

                    <!-- Profile List -->
                    <div id="profile-list" class="space-y-3 hidden">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('search-form');
    const searchLoading = document.getElementById('search-loading');
    const profileList = document.getElementById('profile-list');
    const emptyState = document.getElementById('empty-state');
    const selectAllBtn = document.getElementById('select-all-btn');
    const importBtn = document.getElementById('import-btn');
    const taskTypeSelect = document.getElementById('task-type');
    const rewardInput = document.getElementById('reward-per-task');
    const quantityInput = document.getElementById('quantity-per-profile');
    const selectedCountEl = document.getElementById('selected-count');
    const estBudgetEl = document.getElementById('est-budget');

    let profiles = [];
    let selectedProfiles = new Set();

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Search for profiles
    searchForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const query = document.getElementById('search-query').value.trim();
        const platform = document.getElementById('search-platform').value;
        const limit = parseInt(document.getElementById('search-limit').value);

        if (!query || query.length < 2) {
            alert('Please enter a search query (at least 2 characters)');
            return;
        }

        // Show loading
        searchForm.classList.add('hidden');
        searchLoading.classList.remove('hidden');
        emptyState.classList.add('hidden');
        profileList.classList.add('hidden');

        try {
            const response = await fetch('{{ route("tasks.create.import.search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ query, platform, limit })
            });

            const data = await response.json();
            
            if (data.success) {
                profiles = data.profiles || [];
                renderProfiles();
            } else {
                alert(data.message || 'Search failed');
            }
        } catch (error) {
            console.error('Search error:', error);
            alert('An error occurred while searching');
        } finally {
            searchForm.classList.remove('hidden');
            searchLoading.classList.add('hidden');
        }
    });

    function renderProfiles() {
        if (profiles.length === 0) {
            emptyState.classList.remove('hidden');
            profileList.classList.add('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        profileList.classList.remove('hidden');
        profileList.innerHTML = profiles.map((profile, index) => `
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-800 rounded-xl hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                <div class="flex items-center gap-4">
                    <input type="checkbox" class="profile-checkbox w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                        data-index="${index}" ${selectedProfiles.has(index) ? 'checked' : ''}>
                    <div class="w-10 h-10 rounded-full ${profile.color || 'bg-gray-400'} flex items-center justify-center">
                        <i class="${profile.icon || 'fas fa-user'} text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">@${profile.handle}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">${profile.display_name || ''} • ${formatFollowers(profile.followers)} followers</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">${profile.profile_url}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    ${profile.verified ? '<span class="px-2 py-1 bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-full text-xs"><i class="fas fa-check-circle"></i> Verified</span>' : ''}
                    <span class="px-2 py-1 bg-gray-200 dark:bg-dark-600 text-gray-600 dark:text-gray-400 rounded-full text-xs capitalize">${profile.platform}</span>
                </div>
            </div>
        `).join('');

        // Add checkbox listeners
        document.querySelectorAll('.profile-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const index = parseInt(this.dataset.index);
                if (this.checked) {
                    selectedProfiles.add(index);
                } else {
                    selectedProfiles.delete(index);
                }
                updatePreview();
            });
        });

        updatePreview();
    }

    function formatFollowers(num) {
        if (!num) return '0';
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
        return num.toString();
    }

    function updatePreview() {
        const count = selectedProfiles.size;
        const reward = parseFloat(rewardInput.value) || 0;
        const qty = parseInt(quantityInput.value) || 0;
        
        selectedCountEl.textContent = count;
        estBudgetEl.textContent = '₦' + (count * reward * qty).toLocaleString();
        
        importBtn.disabled = count === 0;
    }

    // Select all
    selectAllBtn.addEventListener('click', function() {
        profiles.forEach((_, index) => selectedProfiles.add(index));
        document.querySelectorAll('.profile-checkbox').forEach(cb => cb.checked = true);
        updatePreview();
    });

    // Update preview on settings change
    [taskTypeSelect, rewardInput, quantityInput].forEach(el => {
        el.addEventListener('change', updatePreview);
        el.addEventListener('input', updatePreview);
    });

    // Import profiles
    importBtn.addEventListener('click', async function() {
        const taskType = taskTypeSelect.value;
        const reward = parseFloat(rewardInput.value);
        const qty = parseInt(quantityInput.value);
        const title = document.getElementById('task-title').value;
        const description = document.getElementById('task-description').value;

        if (!taskType) {
            alert('Please select a task type');
            return;
        }

        if (selectedProfiles.size === 0) {
            alert('Please select at least one profile');
            return;
        }

        const selectedData = profiles.filter((_, i) => selectedProfiles.has(i));
        
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';

        try {
            const response = await fetch('{{ route("tasks.create.import.do") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    profiles: selectedData,
                    task_type: taskType,
                    reward_per_task: reward,
                    quantity_per_profile: qty,
                    task_title: title,
                    task_description: description
                })
            });

            const data = await response.json();
            
            if (data.success) {
                alert(data.message);
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                alert(data.message || 'Import failed');
            }
        } catch (error) {
            console.error('Import error:', error);
            alert('An error occurred while importing');
        } finally {
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="fas fa-download mr-2"></i>Create Tasks';
        }
    });
});
</script>
@endsection