<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hovertask - Create New Task</title>
    
    {{-- Laravel Mix Assets - Tailwind CSS compiled via Mix --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}" defer></script>
    
    {{-- External Libraries --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <link id="heading-font-link" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link id="body-font-link" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --font-heading-name: 'Plus Jakarta Sans';
            --font-body-name: 'Inter';
            --font-heading: 'Plus Jakarta Sans', sans-serif;
            --font-body: 'Inter', sans-serif;
            --letter-spacing-heading: -0.02em;
            --letter-spacing-body: 0px;
            --space-base: 1rem;
            --radius-small: 0.375rem;
            --radius-large: 0.75rem;
            --border-width: 1px;
            --shadow-color: 148 163 184;
            --shadow-opacity: 0.1;
            --shadow-custom: 0 4px 6px -1px rgba(var(--shadow-color), var(--shadow-opacity)), 0 2px 4px -1px rgba(var(--shadow-color), var(--shadow-opacity));
            --shadow-custom-hover: 0 10px 15px -3px rgba(var(--shadow-color), 0.15), 0 4px 6px -2px rgba(var(--shadow-color), 0.1);
        }

        .toggle-checkbox:checked {
            right: 0;
            border-color: #2563eb;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #2563eb;
        }
        
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="font-body bg-neutral-50 text-neutral-900" style="height: auto; min-height: 100%;">

    <!-- Navigation (Simplified for context) -->
    <nav class="bg-white border-b border-neutral-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-primary-600 rounded-small flex items-center justify-center text-white font-bold text-xl">H</div>
                    <span class="font-heading font-bold text-xl text-neutral-900">Hovertask</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden md:flex flex-col items-end mr-2">
                        <span class="text-xs text-neutral-500">Available Balance</span>
                        <span class="font-bold text-neutral-900">₦45,200.00</span>
                    </div>
                    <div class="h-8 w-8 rounded-full bg-neutral-200 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="User" class="h-full w-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="font-heading text-3xl font-bold text-neutral-900">Create a New Task</h1>
            <p class="mt-2 text-neutral-600">Set up your social media task and reach earners instantly.</p>
            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-sm font-medium border border-primary-100">
                <i class="fas fa-info-circle mr-2"></i> Minimum activation required: ₦500 task credit
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Left Column: Task Creation Form -->
            <div class="flex-1">
                <form id="taskForm" class="space-y-6">
                    
                    <!-- Section 1: Task Details -->
                    <section class="bg-white rounded-large shadow-custom p-6 border border-neutral-200">
                        <h2 class="font-heading text-lg font-semibold text-neutral-900 mb-4 flex items-center">
                            <span class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-sm mr-3">1</span>
                            Task Details
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="col-span-1">
                                <label for="platform" class="block text-sm font-medium text-neutral-700 mb-1">Platform</label>
                                <div class="relative">
                                    <select id="platform" class="block w-full pl-10 pr-4 py-3 text-base border-neutral-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-small border bg-white" onchange="updatePreview()">
                                        <option value="instagram">Instagram</option>
                                        <option value="twitter">Twitter / X</option>
                                        <option value="tiktok">TikTok</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="youtube">YouTube</option>
                                    </select>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fab fa-instagram text-neutral-400" id="platformIcon"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-span-1">
                                <label for="taskType" class="block text-sm font-medium text-neutral-700 mb-1">Action Type</label>
                                <select id="taskType" class="block w-full pl-3 pr-10 py-3 text-base border-neutral-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-small border bg-white" onchange="updatePreview()">
                                    <option value="Like">Like Post</option>
                                    <option value="Follow">Follow Account</option>
                                    <option value="Repost">Repost / Retweet</option>
                                    <option value="Comment">Comment</option>
                                    <option value="Share">Share to Story</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="taskLink" class="block text-sm font-medium text-neutral-700 mb-1">Link to Task</label>
                            <input type="url" name="taskLink" id="taskLink" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-neutral-300 rounded-small border p-3" placeholder="https://instagram.com/p/..." required>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-neutral-700 mb-1">Instructions for Earners</label>
                            <textarea id="description" name="description" rows="3" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-neutral-300 rounded-small border p-3" placeholder="e.g., Like the post and comment 'Awesome!'..." oninput="updatePreview()"></textarea>
                            <p class="mt-1 text-xs text-neutral-500">Be specific to ensure quality completions.</p>
                        </div>
                    </section>

                    <!-- Section 2: Budget & Quantity -->
                    <section class="bg-white rounded-large shadow-custom p-6 border border-neutral-200">
                        <h2 class="font-heading text-lg font-semibold text-neutral-900 mb-4 flex items-center">
                            <span class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-sm mr-3">2</span>
                            Budget & Quantity
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-neutral-700 mb-1">Number of Actions</label>
                                <div class="relative rounded-md shadow-sm">
                                    <input type="number" name="quantity" id="quantity" class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-3 pr-12 sm:text-sm border-neutral-300 rounded-small border p-3" placeholder="100" value="50" min="10" oninput="calculateTotal()">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-neutral-500 sm:text-sm">users</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="reward" class="block text-sm font-medium text-neutral-700 mb-1">Reward per Action (₦)</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-neutral-500 sm:text-sm">₦</span>
                                    </div>
                                    <input type="number" name="reward" id="reward" class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-8 pr-12 sm:text-sm border-neutral-300 rounded-small border p-3" placeholder="20" value="20" min="20" oninput="calculateTotal()">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-neutral-500 sm:text-sm">/user</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-neutral-500">Min: ₦20 | Recommended: ₦30 for faster speed</p>
                            </div>
                        </div>

                        <!-- Featured Toggle -->
                        <div class="mt-6 flex items-center justify-between bg-neutral-50 p-4 rounded-small border border-neutral-200">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-neutral-900">Featured Task <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Recommended</span></span>
                                <span class="text-xs text-neutral-500">Get 3x faster completion and top placement.</span>
                            </div>
                            <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="featured" id="featured" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer border-neutral-300" onchange="calculateTotal()"/>
                                <label for="featured" class="toggle-label block overflow-hidden h-6 rounded-full bg-neutral-300 cursor-pointer"></label>
                            </div>
                        </div>
                    </section>
                </form>
            </div>

            <!-- Right Column: Preview & Summary (Sticky) -->
            <div class="w-full lg:w-96 flex flex-col gap-6">
                
                <!-- Preview Card -->
                <div class="bg-white rounded-large shadow-custom border border-neutral-200 overflow-hidden">
                    <div class="bg-neutral-50 px-4 py-3 border-b border-neutral-200 flex justify-between items-center">
                        <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Earner Preview</span>
                        <span class="text-xs text-primary-600 font-medium bg-primary-50 px-2 py-1 rounded-full">Live</span>
                    </div>
                    <div class="p-5">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-gradient-to-tr from-yellow-400 via-red-500 to-purple-500 flex items-center justify-center text-white text-xl" id="previewIconBg">
                                    <i class="fab fa-instagram" id="previewIcon"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 truncate">
                                    <span id="previewAction">Like Post</span> on <span id="previewPlatform">Instagram</span>
                                </p>
                                <p class="text-xs text-neutral-500 mt-1 line-clamp-2" id="previewDesc">
                                    Like the post and comment 'Awesome!'...
                                </p>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ₦<span id="previewReward">20</span>
                                    </span>
                                    <span class="text-xs text-neutral-400">
                                        <i class="far fa-clock mr-1"></i> 2 mins
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button class="w-full flex justify-center py-2 px-4 border border-transparent rounded-small shadow-sm text-sm font-medium text-white bg-neutral-900 hover:bg-neutral-800 focus:outline-none cursor-default opacity-90">
                                Perform Task
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cost Summary -->
                <div class="bg-white rounded-large shadow-custom border border-neutral-200 p-6 sticky top-24">
                    <h3 class="font-heading text-lg font-semibold text-neutral-900 mb-4">Cost Summary</h3>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-neutral-600">
                            <span>Base Cost (<span id="summaryQty">50</span> x ₦<span id="summaryRate">20</span>)</span>
                            <span>₦<span id="summaryBase">1,000</span></span>
                        </div>
                        <div class="flex justify-between text-neutral-600" id="featuredRow" style="display: none;">
                            <span>Featured Fee (Flat)</span>
                            <span>₦500</span>
                        </div>
                        <div class="flex justify-between text-neutral-600">
                            <span>Platform Fee (5%)</span>
                            <span>₦<span id="summaryFee">50</span></span>
                        </div>
                        <div class="border-t border-neutral-200 pt-3 mt-3 flex justify-between items-center">
                            <span class="font-bold text-neutral-900 text-base">Total Pay</span>
                            <span class="font-heading font-bold text-2xl text-primary-600">₦<span id="summaryTotal">1,050</span></span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button onclick="openModal()" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-small shadow-custom text-base font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                            Create Task & Pay
                        </button>
                        <p class="mt-3 text-xs text-center text-neutral-500">
                            By creating this task, you agree to our <a href="{{ route('legal.terms') }}" class="text-primary-600 hover:underline">Terms of Service</a>.
                        </p>
                    </div>
                </div>

                <!-- Helper Tips -->
                <div class="bg-blue-50 rounded-large border border-blue-100 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Pro Tip</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Tasks with clear instructions and rewards above ₦30 are completed 40% faster by our top earners.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-neutral-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>

            <!-- Modal panel -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-large text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-neutral-900" id="modal-title">Confirm Task Creation</h3>
                            <div class="mt-2">
                                <p class="text-sm text-neutral-500">
                                    You are about to create a <span id="modalTaskType" class="font-bold">Like</span> task on <span id="modalPlatform" class="font-bold">Instagram</span>.
                                </p>
                                <div class="mt-4 bg-neutral-50 p-4 rounded-small border border-neutral-200">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-neutral-600">Total Amount:</span>
                                        <span class="text-lg font-bold text-neutral-900">₦<span id="modalTotal">1,050</span></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-neutral-600">Wallet Balance:</span>
                                        <span class="text-sm font-medium text-green-600">₦45,200.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-neutral-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-small border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" onclick="submitTask()">
                        Confirm & Pay
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-small border border-neutral-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-neutral-700 hover:bg-neutral-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeModal()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Platform Config for Icons and Colors
        const platformConfig = {
            instagram: { icon: 'fa-instagram', bg: 'bg-gradient-to-tr from-yellow-400 via-red-500 to-purple-500', text: 'text-white' },
            twitter: { icon: 'fa-twitter', bg: 'bg-black', text: 'text-white' },
            tiktok: { icon: 'fa-tiktok', bg: 'bg-black', text: 'text-white' },
            facebook: { icon: 'fa-facebook-f', bg: 'bg-blue-600', text: 'text-white' },
            youtube: { icon: 'fa-youtube', bg: 'bg-red-600', text: 'text-white' }
        };

        function updatePreview() {
            const platform = document.getElementById('platform').value;
            const type = document.getElementById('taskType').value;
            const desc = document.getElementById('description').value;
            
            document.getElementById('previewPlatform').textContent = platform.charAt(0).toUpperCase() + platform.slice(1);
            document.getElementById('previewAction').textContent = type;
            document.getElementById('previewDesc').textContent = desc || "Like the post and comment 'Awesome!'...";
            
            const config = platformConfig[platform];
            const iconEl = document.getElementById('previewIcon');
            const bgEl = document.getElementById('previewIconBg');
            const inputIcon = document.getElementById('platformIcon');
            
            iconEl.className = `fab ${config.icon}`;
            bgEl.className = `h-12 w-12 rounded-full flex items-center justify-center text-xl ${config.bg} ${config.text}`;
            inputIcon.className = `fab ${config.icon} text-neutral-400`;
        }

        function calculateTotal() {
            const qty = parseInt(document.getElementById('quantity').value) || 0;
            const rate = parseInt(document.getElementById('reward').value) || 0;
            const isFeatured = document.getElementById('featured').checked;
            
            const baseCost = qty * rate;
            const featuredFee = isFeatured ? 500 : 0;
            const platformFee = Math.round(baseCost * 0.05);
            const total = baseCost + featuredFee + platformFee;

            document.getElementById('previewReward').textContent = rate;
            document.getElementById('summaryQty').textContent = qty;
            document.getElementById('summaryRate').textContent = rate;
            document.getElementById('summaryBase').textContent = baseCost.toLocaleString();
            document.getElementById('summaryFee').textContent = platformFee.toLocaleString();
            document.getElementById('summaryTotal').textContent = total.toLocaleString();
            
            document.getElementById('featuredRow').style.display = isFeatured ? 'flex' : 'none';
        }

        function openModal() {
            const total = document.getElementById('summaryTotal').textContent;
            const type = document.getElementById('taskType').value;
            const platform = document.getElementById('platform').value;

            document.getElementById('modalTotal').textContent = total;
            document.getElementById('modalTaskType').textContent = type;
            document.getElementById('modalPlatform').textContent = platform.charAt(0).toUpperCase() + platform.slice(1);
            
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        function submitTask() {
            const btn = document.querySelector('#confirmModal button.bg-primary-600');
            const originalText = btn.textContent;
            btn.textContent = 'Processing...';
            btn.disabled = true;
            
            setTimeout(() => {
                alert('Task created successfully!');
                closeModal();
                btn.textContent = originalText;
                btn.disabled = false;
            }, 1500);
        }

        // Initialize
        calculateTotal();
        updatePreview();
    </script>
</body>
</html>