@extends('layouts.app')

@section('title', 'Service Purchases - SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">Client workspace</p>
                <h1 class="marketplace-title">Service purchases</h1>
                <p class="marketplace-subtitle">Track purchased services from escrow funding through delivery, revision and completion.</p>
            </div>
            <a href="{{ route('professional-services.index') }}" class="marketplace-btn-primary"><i class="fas fa-compass"></i>Browse services</a>
        </div>

        @include('professional-services.partials.workspace-nav', ['activeWorkspace' => 'orders'])

        @php($allOrders = ($activeOrders ?? collect())->merge($completedOrders ?? collect()))
        @if($allOrders->isEmpty())
            <div class="marketplace-card px-6 py-16 text-center">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-indigo-500/10 text-indigo-300"><i class="fas fa-bag-shopping"></i></span>
                <h2 class="mt-4 text-lg font-semibold text-white">No service purchases yet</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">When you purchase a professional service, its status, seller and next action will appear here.</p>
                <a href="{{ route('professional-services.index') }}" class="marketplace-btn-primary mt-5">Browse services</a>
            </div>
        @else
            @foreach([['title' => 'Active purchases', 'items' => $activeOrders ?? collect()], ['title' => 'Completed purchases', 'items' => $completedOrders ?? collect()]] as $group)
                @if($group['items']->isNotEmpty())
                    <section class="mb-8">
                        <div class="mb-3 flex items-end justify-between gap-3"><div><h2 class="text-lg font-semibold text-white">{{ $group['title'] }}</h2><p class="mt-1 text-sm text-slate-500">{{ $group['items']->count() }} order{{ $group['items']->count() === 1 ? '' : 's' }}</p></div></div>
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
                                        'delivered', 'revision' => 'Review delivery',
                                        'paid', 'in_progress' => 'Track progress',
                                        'completed' => 'View completed order',
                                        default => 'View order',
                                    };
                                @endphp
                                <article class="marketplace-card p-5 sm:p-6">
                                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2"><span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $statusClass }}">{{ str_replace('_', ' ', $order->status) }}</span><span class="text-xs text-slate-500">Order #{{ $order->id }} · {{ $order->created_at->diffForHumans() }}</span></div>
                                            <h3 class="mt-3 text-lg font-semibold text-white">{{ $order->service->title ?? 'Service unavailable' }}</h3>
                                            <p class="mt-1 text-sm text-slate-400">Seller: {{ $order->seller->name ?? 'Seller' }}</p>
                                            <div class="mt-3 flex flex-wrap gap-2"><span class="marketplace-pill">₦{{ number_format((float) $order->total_amount, 2) }}</span>@if($order->service)<span class="marketplace-pill">{{ $order->service->delivery_days }} day delivery</span>@endif</div>
                                        </div>
                                        <div class="flex flex-wrap gap-2 lg:w-60 lg:flex-col">
                                            <a href="{{ route('professional-services.orders.show', $order->id) }}" class="marketplace-btn-primary">{{ $nextAction }}</a>
                                            <a href="{{ route('chat.open', ['type' => 'professional_service', 'referenceId' => $order->service_id, 'participantId' => $order->seller_id]) }}" class="marketplace-btn-secondary"><i class="far fa-comment"></i>Message seller</a>
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
