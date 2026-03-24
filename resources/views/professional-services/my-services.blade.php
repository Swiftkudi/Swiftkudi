@extends('layouts.app')

@section('title', 'My Services - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">My Services</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Manage your professional service listings</p>
            </div>
            <a href="{{ route('professional-services.create') }}" 
                class="px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium hover:opacity-90 transition-all shadow-lg shadow-indigo-500/30">
                + Create New Service
            </a>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-dark-700 mb-6">
            <nav class="flex space-x-8">
                <button onclick="showTab('active')" class="py-3 px-1 border-b-2 font-medium text-sm tab-btn" data-tab="active">
                    Active ({{ $activeServices->count() }})
                </button>
                <button onclick="showTab('pending')" class="py-3 px-1 border-b-2 font-medium text-sm tab-btn" data-tab="pending">
                    Pending Review ({{ $pendingServices->count() }})
                </button>
                <button onclick="showTab('draft')" class="py-3 px-1 border-b-2 font-medium text-sm tab-btn" data-tab="draft">
                    Drafts ({{ $draftServices->count() }})
                </button>
            </nav>
        </div>

        <!-- Active Services -->
        <div id="tab-active" class="tab-content">
            @if($activeServices->isEmpty())
                <div class="text-center py-12 bg-white dark:bg-dark-900 rounded-2xl shadow-lg">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-briefcase text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">No active services yet</p>
                    <a href="{{ route('professional-services.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mt-2 inline-block">
                        Create your first service
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($activeServices as $service)
                        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-2">{{ $service->title }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2">{{ $service->description }}</p>
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">₦{{ number_format($service->price) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $service->delivery_days }} days</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Pending Services -->
        <div id="tab-pending" class="tab-content hidden">
            @if($pendingServices->isEmpty())
                <div class="text-center py-12 bg-white dark:bg-dark-900 rounded-2xl shadow-lg">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clock text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">No pending services</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pendingServices as $service)
                        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400 text-xs rounded">Pending Review</span>
                            </div>
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-2">{{ $service->title }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2">{{ $service->description }}</p>
                            <span class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">₦{{ number_format($service->price) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Draft Services -->
        <div id="tab-draft" class="tab-content hidden">
            @if($draftServices->isEmpty())
                <div class="text-center py-12 bg-white dark:bg-dark-900 rounded-2xl shadow-lg">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-alt text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">No drafts</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($draftServices as $service)
                        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 text-gray-800 dark:text-gray-200 text-xs rounded">Draft</span>
                            </div>
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-2">{{ $service->title }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2">{{ $service->description }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.tab-btn.active {
    border-color: #4f46e5;
    color: #4f46e5;
}
.tab-btn {
    border-color: transparent;
    color: #6b7280;
}
.tab-btn:hover {
    border-color: #9ca3af;
}
</style>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById('tab-' + tab).classList.remove('hidden');
    document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
}

// Show active tab by default
showTab('active');
</script>
@endsection
