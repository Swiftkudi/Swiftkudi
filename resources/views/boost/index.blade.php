@extends('layouts.app')

@section('title', 'Boost & Promotion - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">Boost & Promotion</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Boost your listings to get more visibility and orders</p>
        </div>

        <!-- Wallet Balance -->
        <div class="mb-6 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100 text-sm">Available Balance</p>
                    <p class="text-3xl font-bold">₦{{ number_format(auth()->user()->wallet->withdrawable_balance ?? 0) }}</p>
                </div>
                <a href="{{ route('wallet.deposit') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-white font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i>Deposit
                </a>
            </div>
        </div>

        <!-- Active Boosts -->
        @if($activeBoosts->count() > 0)
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Your Active Boosts</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($activeBoosts as $boost)
                        <div class="bg-white dark:bg-dark-900 rounded-xl shadow border border-gray-100 dark:border-dark-700 p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-1 bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400 text-xs font-medium rounded-full">
                                    {{ $boost->package->name }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    Expires {{ $boost->expires_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="capitalize">{{ $boost->target_type }}</span>: #{{ $boost->target_id }}
                            </div>
                            <div class="mt-3 flex gap-2">
                                <form action="{{ route('boost.extend', $boost->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="package_id" value="{{ $boost->package_id }}">
                                    <button type="submit" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                                        <i class="fas fa-clock mr-1"></i>Extend
                                    </button>
                                </form>
                                <form action="{{ route('boost.cancel', $boost->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:text-red-800" onclick="return confirm('Cancel this boost? You will receive a partial refund.')">
                                        <i class="fas fa-times mr-1"></i>Cancel
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Boost Packages -->
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Choose a Boost Package</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($packages as $package)
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6 hover:border-pink-300 dark:hover:border-pink-500 transition-colors {{ $package->is_featured ? 'ring-2 ring-pink-500' : '' }}">
                    @if($package->is_featured)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="px-3 py-1 bg-pink-500 text-white text-xs font-bold rounded-full">POPULAR</span>
                        </div>
                    @endif
                    <div class="text-center mb-4">
                        <h3 class="font-bold text-xl text-gray-900 dark:text-gray-100">{{ $package->name }}</h3>
                        <div class="mt-2">
                            <span class="text-3xl font-bold text-pink-600 dark:text-pink-400">₦{{ number_format($package->price) }}</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $package->duration_days }} days duration</p>
                        @if($package->boost_multiplier > 1)
                            <p class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">{{ $package->boost_multiplier }}x visibility boost</p>
                        @endif
                    </div>

                    <ul class="space-y-2 mb-6">
                        @foreach(json_decode($package->features, true) ?? [] as $feature)
                            <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <i class="fas fa-check text-green-500"></i>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    <button type="button" onclick="openBoostModal({{ $package->id }}, '{{ $package->name }}', {{ $package->price }}, {{ $package->duration_days }})" 
                            class="w-full px-4 py-3 bg-gradient-to-r from-pink-600 to-purple-600 hover:from-pink-700 hover:to-purple-700 text-white font-medium rounded-xl transition-colors">
                        Select Package
                    </button>
                </div>
            @endforeach
        </div>

        <!-- Info Section -->
        <div class="mt-8 bg-gradient-to-r from-pink-50 to-purple-50 dark:from-pink-900/10 dark:to-purple-900/10 rounded-2xl p-6">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">How Boosting Works</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex gap-4">
                    <div class="w-10 h-10 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-pink-600 dark:text-pink-400 font-bold">1</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Choose a Package</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Select the boost duration and features that suit your needs</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-10 h-10 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-pink-600 dark:text-pink-400 font-bold">2</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Select Target</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Choose which task, service, or product you want to boost</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-10 h-10 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-pink-600 dark:text-pink-400 font-bold">3</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Get More Views</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Your listing gets featured and attracts more buyers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Boost Modal -->
<div id="boost-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-dark-900 rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-pink-600 to-purple-600 p-6 rounded-t-3xl">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">Boost Your Listing</h2>
                    <p class="text-pink-200 mt-1" id="modal-package-name">Package Name</p>
                </div>
                <button onclick="closeBoostModal()" class="text-white/80 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <form id="boost-form" action="{{ route('boost.activate') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <!-- Package Info -->
            <div class="bg-gradient-to-r from-pink-50 to-purple-50 dark:from-pink-900/20 dark:to-purple-900/20 rounded-xl p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Package</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100" id="modal-package-display">Pro Boost</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Price</p>
                        <p class="font-bold text-pink-600 dark:text-pink-400" id="modal-price-display">₦1,500</p>
                    </div>
                </div>
                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Duration: <span id="modal-duration-display">7</span> days
                </div>
            </div>

            <input type="hidden" name="package_id" id="selected-package-id">

            <!-- Item Type Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-tag mr-2 text-pink-500"></i>
                    What would you like to boost?
                </label>
                <select name="target_type" id="target-type-select" 
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                        onchange="loadItems(this.value)">
                    <option value="">Select item type...</option>
                    <option value="task">Task</option>
                    <option value="service">Professional Service</option>
                    <option value="product">Digital Product</option>
                    <option value="growth">Growth Listing</option>
                </select>
            </div>

            <!-- Item Selection -->
            <div id="items-container" class="hidden">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-list mr-2 text-pink-500"></i>
                    Select Your Listing
                </label>
                <select name="target_id" id="target-id-select" 
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    <option value="">Loading...</option>
                </select>
            </div>

            <!-- No Items Message -->
            <div id="no-items-message" class="hidden text-center py-4">
                <i class="fas fa-box-open text-4xl text-gray-300 dark:text-gray-600 mb-2"></i>
                <p class="text-gray-500 dark:text-gray-400">You don't have any active listings of this type.</p>
                <a href="#" id="create-listing-link" class="text-pink-600 dark:text-pink-400 hover:text-pink-700 text-sm">
                    Create one now <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3">
                <button type="button" onclick="closeBoostModal()" 
                        class="flex-1 py-3 bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-300 dark:hover:bg-dark-600 transition-colors">
                    Cancel
                </button>
                <button type="submit" id="submit-boost-btn"
                        class="flex-1 py-3 bg-gradient-to-r from-pink-600 to-purple-600 text-white rounded-xl font-bold hover:from-pink-700 hover:to-purple-700 transition-all shadow-lg shadow-pink-500/30 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <i class="fas fa-rocket mr-2"></i>Purchase Boost
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let selectedPackage = null;
    const userItems = @json($userItems);

    function openBoostModal(packageId, packageName, price, duration) {
        selectedPackage = { id: packageId, name: packageName, price: price, duration: duration };
        
        document.getElementById('selected-package-id').value = packageId;
        document.getElementById('modal-package-name').textContent = packageName;
        document.getElementById('modal-package-display').textContent = packageName;
        document.getElementById('modal-price-display').textContent = '₦' + price.toLocaleString();
        document.getElementById('modal-duration-display').textContent = duration;
        
        // Reset selections
        document.getElementById('target-type-select').value = '';
        document.getElementById('items-container').classList.add('hidden');
        document.getElementById('no-items-message').classList.add('hidden');
        document.getElementById('submit-boost-btn').disabled = true;
        
        document.getElementById('boost-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeBoostModal() {
        document.getElementById('boost-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function loadItems(type) {
        const itemsContainer = document.getElementById('items-container');
        const noItemsMessage = document.getElementById('no-items-message');
        const targetIdSelect = document.getElementById('target-id-select');
        const submitBtn = document.getElementById('submit-boost-btn');
        
        if (!type) {
            itemsContainer.classList.add('hidden');
            noItemsMessage.classList.add('hidden');
            submitBtn.disabled = true;
            return;
        }

        // Filter items by type
        const filteredItems = userItems.filter(item => item.type === type);
        
        if (filteredItems.length === 0) {
            itemsContainer.classList.add('hidden');
            noItemsMessage.classList.remove('hidden');
            submitBtn.disabled = true;
            
            // Update create link based on type
            const createLinks = {
                'task': '{{ route("tasks.create") }}',
                'service': '{{ route("professional-services.create") }}',
                'product': '{{ route("digital-products.create") }}',
                'growth': '{{ route("growth.create") }}'
            };
            document.getElementById('create-listing-link').href = createLinks[type] || '#';
        } else {
            noItemsMessage.classList.add('hidden');
            itemsContainer.classList.remove('hidden');
            
            // Populate select
            targetIdSelect.innerHTML = '<option value="">Select a listing...</option>';
            filteredItems.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.title;
                targetIdSelect.appendChild(option);
            });
        }
    }

    // Enable submit button when item is selected
    document.getElementById('target-id-select').addEventListener('change', function() {
        const submitBtn = document.getElementById('submit-boost-btn');
        submitBtn.disabled = !this.value;
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBoostModal();
        }
    });

    // Close modal on backdrop click
    document.getElementById('boost-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeBoostModal();
        }
    });
</script>
@endpush
@endsection
