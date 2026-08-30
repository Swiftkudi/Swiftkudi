@extends('layouts.app')

@section('title', 'My Contracts | SwiftKudi')
@section('meta_description', 'Manage your active and completed SwiftKudi contracts.')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">My work</p>
                <h1 class="marketplace-title">Contracts</h1>
                <p class="marketplace-subtitle">One workroom for scope, milestones, submissions and escrow-backed payments.</p>
            </div>
            <a href="{{ route('jobs.index') }}" class="marketplace-btn-primary"><i class="fas fa-search"></i>Find work</a>
        </div>

        <div class="marketplace-card mb-5 p-3">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                @foreach(['' => 'All', 'active' => 'Active', 'completed' => 'Completed', 'disputed' => 'Disputed', 'cancelled' => 'Cancelled'] as $value => $label)
                    <button name="status" value="{{ $value }}" class="rounded-lg px-3.5 py-2 text-sm font-semibold {{ request('status', '') === $value ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-dark-800 hover:text-white' }}">{{ $label }}</button>
                @endforeach
            </form>
        </div>

        <div class="space-y-4">
            @forelse($contracts as $contract)
                @php
                    $other = auth()->id() === $contract->client_id ? $contract->freelancer : $contract->client;
                    $role = auth()->id() === $contract->client_id ? 'Client' : 'Freelancer';
                    $statusClass = $contract->status === 'completed' ? 'text-emerald-300 bg-emerald-500/10 border-emerald-500/20' : ($contract->status === 'active' ? 'text-indigo-300 bg-indigo-500/10 border-indigo-500/20' : 'text-gray-300 bg-dark-800 border-dark-600');
                @endphp
                <a href="{{ route('contracts.show', $contract) }}" class="marketplace-card-hover block p-5 sm:p-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $statusClass }}">{{ $contract->status }}</span>
                                <span class="text-xs text-gray-500">{{ $contract->contract_no }}</span>
                            </div>
                            <h2 class="truncate text-lg font-semibold text-white">{{ $contract->title }}</h2>
                            <p class="mt-1 text-sm text-gray-400">{{ $role }} relationship with <span class="font-medium text-gray-300">{{ $other->name ?? 'User' }}</span></p>
                        </div>
                        <div class="grid grid-cols-3 gap-6 text-left lg:text-right">
                            <div><p class="text-xs text-gray-500">Value</p><p class="mt-1 text-sm font-semibold text-white">₦{{ number_format((float) $contract->amount, 2) }}</p></div>
                            <div><p class="text-xs text-gray-500">Milestones</p><p class="mt-1 text-sm font-semibold text-white">{{ $contract->milestones->count() }}</p></div>
                            <div><p class="text-xs text-gray-500">Progress</p><p class="mt-1 text-sm font-semibold text-white">{{ $contract->progress_percent }}%</p></div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="marketplace-card px-6 py-16 text-center"><i class="far fa-file-lines mb-4 block text-4xl text-gray-600"></i><h2 class="text-lg font-semibold text-white">No contracts yet</h2><p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-gray-500">When a client hires a freelancer from a proposal, SwiftKudi creates a contract workroom here.</p><a href="{{ route('jobs.index') }}" class="marketplace-btn-primary mt-6">Explore jobs</a></div>
            @endforelse
        </div>

        @if($contracts->hasPages())<div class="mt-6">{{ $contracts->links() }}</div>@endif
    </div>
</div>
@endsection
