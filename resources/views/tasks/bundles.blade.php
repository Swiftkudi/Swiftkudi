@extends('layouts.app')

@section('title', 'Task Bundles - SwiftKudi')

@section('content')
<div class="py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Task Bundles</h1>
            <p class="text-gray-600 mt-1">Complete bundled tasks for bigger rewards!</p>
        </div>

        <!-- Info Banner -->
        <div class="bg-gradient-to-r from-green-500 to-teal-600 rounded-lg p-6 mb-8 text-white">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-box-open text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold mb-2">Bundle Rewards = Bigger Earnings!</h2>
                    <p class="opacity-90">We've combined multiple micro tasks into bundles so you can earn more per completion. Complete a bundle and get rewarded with bonus ₦!</p>
                </div>
            </div>
        </div>

        @if($bundles->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($bundles as $bundle)
                    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Bundle Header -->
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-4 text-white">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-lg truncate">{{ $bundle->name }}</h3>
                                @if($bundle->is_active && (!$bundle->expires_at || $bundle->expires_at->isFuture()))
                                    <span class="bg-green-400 text-green-900 text-xs px-2 py-1 rounded-full font-medium">
                                        Active
                                    </span>
                                @else
                                    <span class="bg-gray-400 text-gray-900 text-xs px-2 py-1 rounded-full font-medium">
                                        Ended
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Bundle Content -->
                        <div class="p-6">
                            <!-- Description -->
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $bundle->description }}</p>

                            <!-- Task Count Badge -->
                            <div class="flex items-center mb-4">
                                <span class="bg-indigo-100 text-indigo-800 text-sm font-medium px-3 py-1 rounded-full">
                                    <i class="fas fa-tasks mr-1"></i>
                                    {{ $bundle->total_tasks }} Tasks
                                </span>
                                <span class="ml-2 bg-purple-100 text-purple-800 text-sm font-medium px-3 py-1 rounded-full">
                                    <i class="fas fa-layer-group mr-1"></i>
                                    {{ ucfirst($bundle->difficulty_level) }}
                                </span>
                            </div>

                            <!-- Category Icons -->
                            @if($bundle->category_ids)
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach(array_slice(json_decode($bundle->category_ids, true) ?: [], 0, 4) as $catId)
                                        @php
                                            $cat = \App\Models\TaskCategory::find($catId);
                                        @endphp
                                        @if($cat)
                                            <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">
                                                <i class="fab fa-{{ $cat->icon ?? 'tasks' }} mr-1"></i>
                                                {{ $cat->name }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <!-- Reward -->
                            <div class="bg-green-50 rounded-lg p-4 mb-4 text-center">
                                <p class="text-sm text-green-600 mb-1">Bundle Reward</p>
                                <p class="text-3xl font-bold text-green-600">₦{{ number_format($bundle->worker_reward, 0) }}</p>
                                <p class="text-xs text-green-500">+ Bonus ₦{{ number_format($bundle->worker_reward * 0.15, 0) }} for completing bundle!</p>
                            </div>

                            <!-- Total Tasks Progress -->
                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Tasks in bundle</span>
                                    <span class="font-medium text-gray-900">{{ $bundle->total_tasks }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: 100%"></div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            @if($bundle->is_active && (!$bundle->expires_at || $bundle->expires_at->isFuture()))
                                <a href="#" class="block w-full py-3 bg-indigo-600 text-white text-center rounded-lg font-medium hover:bg-indigo-700 transition">
                                    <i class="fas fa-play mr-2"></i> Start Bundle
                                </a>
                            @else
                                <button disabled class="block w-full py-3 bg-gray-300 text-gray-500 text-center rounded-lg font-medium cursor-not-allowed">
                                    <i class="fas fa-lock mr-2"></i> Bundle Ended
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- No Bundles Available -->
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-box-open text-gray-400 text-4xl"></i>
                </div>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No Bundles Available</h3>
                <p class="text-gray-500 mb-6">Check back later for exciting task bundles!</p>
                <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                    <i class="fas fa-tasks mr-2"></i> Browse Individual Tasks
                </a>
            </div>
        @endif

        <!-- How Bundles Work -->
        <div class="mt-12 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">How Task Bundles Work</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-indigo-600 font-bold text-xl">1</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Choose a Bundle</h3>
                    <p class="text-gray-600 text-sm">Browse available bundles and pick one that interests you</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-indigo-600 font-bold text-xl">2</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Complete Tasks</h3>
                    <p class="text-gray-600 text-sm">Work through all tasks in the bundle one by one</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-indigo-600 font-bold text-xl">3</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Earn Rewards</h3>
                    <p class="text-gray-600 text-sm">Get your task rewards plus a bundle completion bonus</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-indigo-600 font-bold text-xl">4</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Level Up</h3>
                    <p class="text-gray-600 text-sm">Earn more XP and unlock premium bundles</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
