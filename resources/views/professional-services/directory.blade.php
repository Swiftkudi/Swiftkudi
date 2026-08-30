@extends('layouts.app')

@section('title', 'Find Freelancers | SwiftKudi')
@section('meta_description', 'Browse available freelancers on SwiftKudi by skill, rating and hourly rate.')
@section('canonical', route('freelancers.index'))

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">Find talent</p>
                <h1 class="marketplace-title">Hire skilled professionals</h1>
                <p class="marketplace-subtitle">Search freelancer profiles, review skills and work history, then start a conversation or purchase a service.</p>
            </div>
            @auth<a href="{{ route('jobs.create') }}" class="marketplace-btn-primary"><i class="fas fa-plus"></i>Post a job</a>@endauth
        </div>

        <div class="marketplace-card mb-6 p-4 sm:p-5">
            <form method="GET" action="{{ route('freelancers.index') }}" class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px_180px_auto]">
                <div class="relative"><i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500"></i><input type="search" name="search" value="{{ request('search') }}" class="marketplace-input pl-10" placeholder="Search by name, title or skill"></div>
                <select name="skill" class="marketplace-input"><option value="">Any skill</option>@foreach($allSkills as $skill)<option value="{{ $skill }}" @selected(request('skill') == $skill)>{{ $skill }}</option>@endforeach</select>
                <input type="number" name="max_rate" value="{{ request('max_rate') }}" min="0" class="marketplace-input" placeholder="Max ₦/hour">
                <button class="marketplace-btn-primary"><i class="fas fa-filter"></i>Search</button>
                <div class="lg:col-span-4 flex flex-wrap items-center gap-3 pt-1 text-xs text-gray-500"><span>Minimum rating:</span>@foreach([0=>'Any',4=>'4.0+',4.5=>'4.5+'] as $rating=>$label)<label class="cursor-pointer"><input type="radio" name="min_rating" value="{{ $rating ?: '' }}" class="mr-1 text-indigo-600 focus:ring-indigo-500" @checked((string)request('min_rating','') === (string)($rating ?: ''))>{{ $label }}</label>@endforeach</div>
            </form>
        </div>

        @if($providers->isEmpty())
            <div class="marketplace-card px-6 py-16 text-center"><i class="fas fa-user-group mb-4 block text-4xl text-gray-600"></i><h2 class="text-lg font-semibold text-white">No freelancers match those filters</h2><p class="mt-2 text-sm text-gray-500">Try a broader skill or rate range.</p></div>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($providers as $profile)
                    <article class="marketplace-card-hover flex flex-col p-5">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-600 text-base font-bold text-white">{{ strtoupper(substr($profile->user->name, 0, 2)) }}</div>
                            <div class="min-w-0 flex-1"><div class="flex items-center gap-2"><h2 class="truncate font-semibold text-white">{{ $profile->user->name }}</h2>@if($profile->is_available)<span class="h-2 w-2 rounded-full bg-emerald-400" title="Available"></span>@endif</div><p class="mt-0.5 line-clamp-1 text-sm text-gray-400">{{ $profile->professional_title ?: 'Independent professional' }}</p><div class="mt-1 flex items-center gap-1 text-xs"><i class="fas fa-star text-amber-400"></i><span class="font-semibold text-gray-200">{{ number_format((float)$profile->average_rating, 1) }}</span><span class="text-gray-500">({{ $profile->total_reviews }} reviews)</span></div></div>
                        </div>

                        @if($profile->bio)<p class="mt-4 line-clamp-3 text-sm leading-6 text-gray-400">{{ $profile->bio }}</p>@endif
                        <div class="mt-4 flex flex-wrap gap-2">@foreach(array_slice($profile->skills ?? [], 0, 5) as $skill)<span class="marketplace-pill">{{ $skill }}</span>@endforeach</div>

                        <div class="mt-auto flex items-end justify-between gap-3 border-t border-dark-700 pt-5 mt-5"><div>@if($profile->hourly_rate)<p class="text-xs text-gray-500">Hourly rate</p><p class="mt-0.5 font-semibold text-white">₦{{ number_format((float)$profile->hourly_rate) }}/hr</p>@else<p class="text-sm text-gray-500">Rate on request</p>@endif</div><a href="{{ $profile->slug ? route('freelancers.show', $profile->slug) : route('professional-services.provider-profile', $profile->user_id) }}" class="text-sm font-semibold text-indigo-400 hover:text-indigo-300">View profile <i class="fas fa-arrow-right ml-1 text-xs"></i></a></div>
                    </article>
                @endforeach
            </div>
            @if($providers->hasPages())<div class="mt-7">{{ $providers->links() }}</div>@endif
        @endif
    </div>
</div>
@endsection
