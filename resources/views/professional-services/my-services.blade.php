@extends('layouts.app')

@section('title', 'My Services - SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">Seller workspace</p>
                <h1 class="marketplace-title">My services</h1>
                <p class="marketplace-subtitle">Manage your service catalog and understand which listings are live, awaiting review or still in draft.</p>
            </div>
            <a href="{{ route('professional-services.create') }}" class="marketplace-btn-primary"><i class="fas fa-plus"></i>Create service</a>
        </div>

        @include('professional-services.partials.workspace-nav', ['activeWorkspace' => 'services'])

        @php
            $serviceTabs = [
                'active' => ['label' => 'Active', 'collection' => $activeServices],
                'pending' => ['label' => 'Pending review', 'collection' => $pendingServices],
                'draft' => ['label' => 'Drafts', 'collection' => $draftServices],
            ];
        @endphp

        <div class="marketplace-card mb-5 p-2" role="tablist" aria-label="Service statuses">
            <div class="flex overflow-x-auto">
                @foreach($serviceTabs as $key => $tab)
                    <button type="button" class="service-tab-btn min-h-[42px] shrink-0 rounded-lg px-4 py-2 text-sm font-semibold text-slate-400 hover:bg-slate-800 hover:text-white" data-tab="{{ $key }}" role="tab" aria-controls="services-{{ $key }}">
                        {{ $tab['label'] }} <span class="ml-1 text-xs opacity-70">{{ $tab['collection']->count() }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        @foreach($serviceTabs as $key => $tab)
            <section id="services-{{ $key }}" class="service-tab-panel hidden" role="tabpanel">
                @if($tab['collection']->isEmpty())
                    <div class="marketplace-card px-6 py-14 text-center">
                        <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-indigo-500/10 text-indigo-300"><i class="fas {{ $key === 'active' ? 'fa-briefcase' : ($key === 'pending' ? 'fa-clock' : 'fa-file-lines') }}"></i></span>
                        <h2 class="mt-4 text-lg font-semibold text-white">No {{ strtolower($tab['label']) }} services</h2>
                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">{{ $key === 'active' ? 'Create a clear service listing to start building your catalog.' : ($key === 'pending' ? 'Services submitted for moderation will appear here.' : 'Draft service listings will appear here when supported by the creation workflow.') }}</p>
                        @if($key === 'active')<a href="{{ route('professional-services.create') }}" class="marketplace-btn-primary mt-5">Create a service</a>@endif
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($tab['collection'] as $service)
                            @php
                                $statusClass = match($service->status) {
                                    'active' => 'border-emerald-500/25 bg-emerald-500/10 text-emerald-300',
                                    'pending' => 'border-amber-500/25 bg-amber-500/10 text-amber-300',
                                    default => 'border-slate-700 bg-slate-800 text-slate-300',
                                };
                            @endphp
                            <article class="marketplace-card flex h-full flex-col p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $statusClass }}">{{ str_replace('_', ' ', $service->status) }}</span>
                                        @if($service->category)<span class="marketplace-pill">{{ $service->category->name }}</span>@endif
                                    </div>
                                    <span class="text-xs text-slate-500">#{{ $service->id }}</span>
                                </div>
                                <h2 class="mt-4 text-lg font-semibold leading-6 text-white">{{ $service->title }}</h2>
                                <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-400">{{ $service->description }}</p>
                                <dl class="mt-5 grid grid-cols-3 gap-2 border-t border-slate-800 pt-4 text-sm">
                                    <div><dt class="text-xs text-slate-500">Price</dt><dd class="mt-1 font-semibold text-white">₦{{ number_format((float) $service->price) }}</dd></div>
                                    <div><dt class="text-xs text-slate-500">Delivery</dt><dd class="mt-1 font-semibold text-white">{{ $service->delivery_days }}d</dd></div>
                                    <div><dt class="text-xs text-slate-500">Orders</dt><dd class="mt-1 font-semibold text-white">{{ $service->orders_count ?? 0 }}</dd></div>
                                </dl>
                                <div class="mt-auto flex flex-wrap gap-2 pt-5">
                                    <a href="{{ route('professional-services.show', $service) }}" class="marketplace-btn-secondary flex-1">View listing</a>
                                    @if($service->status === 'active')
                                        <a href="{{ route('professional-services.sales.index') }}" class="marketplace-btn-secondary">Sales</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach
    </div>
</div>

<script>
(function () {
    const buttons = Array.from(document.querySelectorAll('.service-tab-btn'));
    const panels = Array.from(document.querySelectorAll('.service-tab-panel'));
    function activate(key) {
        buttons.forEach((button) => {
            const active = button.dataset.tab === key;
            button.classList.toggle('bg-indigo-600', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('text-slate-400', !active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        panels.forEach((panel) => panel.classList.toggle('hidden', panel.id !== `services-${key}`));
    }
    buttons.forEach((button) => button.addEventListener('click', () => activate(button.dataset.tab)));
    activate('active');
})();
</script>
@endsection
