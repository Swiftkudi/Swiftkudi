@extends('layouts.app')

@section('title', 'Service Sales - SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">Seller workspace</p>
                <h1 class="marketplace-title">Service sales</h1>
                <p class="marketplace-subtitle">See what requires delivery, revision or follow-up and keep completed work easy to reference.</p>
            </div>
            <a href="{{ route('professional-services.create') }}" class="marketplace-btn-primary"><i class="fas fa-plus"></i>Create service</a>
        </div>

        @include('professional-services.partials.workspace-nav', ['activeWorkspace' => 'sales'])

        @php($allSales = ($activeSales ?? collect())->merge($completedSales ?? collect()))
        @if($allSales->isEmpty())
            <div class="marketplace-card px-6 py-16 text-center">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-indigo-500/10 text-indigo-300"><i class="fas fa-chart-line"></i></span>
                <h2 class="mt-4 text-lg font-semibold text-white">No service sales yet</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">Active client purchases will appear here with the next action required from you.</p>
                <a href="{{ route('professional-services.create') }}" class="marketplace-btn-primary mt-5">Create a service</a>
            </div>
        @else
            @foreach([['title' => 'Active sales', 'items' => $activeSales ?? collect()], ['title' => 'Completed sales', 'items' => $completedSales ?? collect()]] as $group)
                @if($group['items']->isNotEmpty())
                    <section class="mb-8">
                        <div class="mb-3"><h2 class="text-lg font-semibold text-white">{{ $group['title'] }}</h2><p class="mt-1 text-sm text-slate-500">{{ $group['items']->count() }} order{{ $group['items']->count() === 1 ? '' : 's' }}</p></div>
                        <div class="space-y-3">
                            @foreach($group['items'] as $order)
                                @php
                                    $statusClass = match($order->status) {
                                        'completed' => 'border-emerald-500/25 bg-emerald-500/10 text-emerald-300',
                                        'delivered' => 'border-indigo-500/25 bg-indigo-500/10 text-indigo-300',
                                        'revision' => 'border-amber-500/25 bg-amber-500/10 text-amber-300',
                                        default => 'border-slate-700 bg-slate-800 text-slate-300',
                                    };
                                    $nextAction = match($order->status) {
                                        'paid', 'in_progress', 'revision' => 'Manage delivery',
                                        'delivered' => 'Await client review',
                                        'completed' => 'View completed sale',
                                        default => 'View order',
                                    };
                                @endphp
                                <article class="marketplace-card p-5 sm:p-6">
                                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2"><span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $statusClass }}">{{ str_replace('_', ' ', $order->status) }}</span><span class="text-xs text-slate-500">Order #{{ $order->id }} · {{ $order->created_at->diffForHumans() }}</span></div>
                                            <h3 class="mt-3 text-lg font-semibold text-white">{{ $order->service->title ?? 'Service unavailable' }}</h3>
                                            <p class="mt-1 text-sm text-slate-400">Client: {{ $order->buyer->name ?? 'Client' }}</p>
                                            <div class="mt-3 flex flex-wrap gap-2"><span class="marketplace-pill">Order ₦{{ number_format((float) $order->total_amount, 2) }}</span>@if($order->status === 'completed')<span class="marketplace-pill">Payout ₦{{ number_format((float) ($order->seller_payout ?? 0), 2) }}</span>@endif</div>
                                        </div>
                                        <div class="flex flex-wrap gap-2 lg:w-60 lg:flex-col">
                                            <a href="{{ route('professional-services.orders.show', $order->id) }}" class="{{ in_array($order->status, ['paid','in_progress','revision'], true) ? 'marketplace-btn-primary' : 'marketplace-btn-secondary' }}">{{ $nextAction }}</a>
                                            <a href="{{ route('chat.open', ['type' => 'professional_service', 'referenceId' => $order->service_id, 'participantId' => $order->buyer_id]) }}" class="marketplace-btn-secondary"><i class="far fa-comment"></i>Message client</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        @endif
    </div>
</div>
@endsection
