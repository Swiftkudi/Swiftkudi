@extends('layouts.app')

@section('title', 'Service Providers Directory - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Service Providers</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Find skilled professionals on SwiftKudi</p>
        </div>

        @if($providers->isEmpty())
            <div class="text-center py-12 bg-white dark:bg-dark-900 rounded-2xl shadow-lg">
                <div class="w-16 h-16 bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-2xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 dark:text-gray-400">No service providers yet</p>
                <a href="{{ route('professional-services.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mt-2 inline-block">
                    Browse services instead
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($providers as $profile)
                    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-lg">{{ substr($profile->user->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $profile->user->name }}</h3>
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= floor($profile->rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                    @endfor
                                    <span class="text-sm text-gray-500 dark:text-gray-400">({{ $profile->completed_orders ?? 0 }} orders)</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($profile->bio)
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">{{ $profile->bio }}</p>
                        @endif
                        
                        @if($profile->skills)
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach(array_slice(json_decode($profile->skills, true) ?? [], 0, 4) as $skill)
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 text-xs rounded">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif
                        
                        <div class="flex justify-between items-center">
                            @if($profile->hourly_rate)
                                <span class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">₦{{ number_format($profile->hourly_rate) }}/hr</span>
                            @endif
                            <a href="{{ route('professional-services.provider-profile', $profile->user_id) }}" 
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                View Profile →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection