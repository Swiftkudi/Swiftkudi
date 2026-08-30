@extends('layouts.app')

@section('title', $contract->title . ' Contract | SwiftKudi')
@section('meta_description', 'Private SwiftKudi contract workroom.')
@section('robots', 'noindex,nofollow')

@section('content')
@php
    $isClient = auth()->id() === $contract->client_id;
    $isFreelancer = auth()->id() === $contract->freelancer_id;
    $other = $isClient ? $contract->freelancer : $contract->client;
@endphp
<div class="marketplace-page">
    <div class="marketplace-container">
        <div class="mb-6 flex flex-wrap items-center gap-2 text-sm text-gray-500"><a href="{{ route('contracts.index') }}" class="hover:text-indigo-300">Contracts</a><i class="fas fa-chevron-right text-[9px]"></i><span class="truncate">{{ $contract->contract_no }}</span></div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-6">
                <section class="marketplace-card p-6">
                    <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div class="mb-2 flex flex-wrap items-center gap-2"><span class="marketplace-pill capitalize">{{ $contract->status }}</span><span class="text-xs text-gray-500">{{ $contract->contract_no }}</span></div>
                            <h1 class="font-heading text-2xl font-bold text-white">{{ $contract->title }}</h1>
                            <p class="mt-2 text-sm text-gray-400">Working with <span class="font-semibold text-gray-200">{{ $other->name ?? 'User' }}</span></p>
                        </div>
                        <div class="rounded-xl border border-dark-700 bg-dark-950 px-5 py-4 md:text-right"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Contract value</p><p class="mt-1 text-xl font-bold text-white">₦{{ number_format((float) $contract->amount, 2) }}</p></div>
                    </div>
                    <div class="mt-6"><div class="mb-2 flex items-center justify-between text-xs"><span class="text-gray-500">Milestone progress</span><span class="font-semibold text-gray-300">{{ $contract->progress_percent }}%</span></div><div class="h-2 overflow-hidden rounded-full bg-dark-800"><div class="h-full rounded-full bg-indigo-500" style="width: {{ $contract->progress_percent }}%"></div></div></div>
                    @if($contract->description)<p class="mt-6 whitespace-pre-line text-sm leading-7 text-gray-400">{{ $contract->description }}</p>@endif
                </section>

                <section>
                    <div class="mb-4 flex items-center justify-between"><div><h2 class="text-lg font-semibold text-white">Milestones</h2><p class="mt-1 text-sm text-gray-500">Fund, deliver and approve work in clear stages.</p></div></div>
                    <div class="space-y-4">
                        @forelse($contract->milestones as $milestone)
                            @php
                                $statusStyles = [
                                    'pending_funding' => 'bg-amber-500/10 text-amber-300 border-amber-500/20',
                                    'funded' => 'bg-indigo-500/10 text-indigo-300 border-indigo-500/20',
                                    'in_progress' => 'bg-blue-500/10 text-blue-300 border-blue-500/20',
                                    'submitted' => 'bg-purple-500/10 text-purple-300 border-purple-500/20',
                                    'revision_requested' => 'bg-orange-500/10 text-orange-300 border-orange-500/20',
                                    'released' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20',
                                ];
                                $statusStyle = $statusStyles[$milestone->status] ?? 'bg-dark-800 text-gray-300 border-dark-600';
                            @endphp
                            <article class="marketplace-card overflow-hidden">
                                <div class="p-5 sm:p-6">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0"><div class="mb-2 flex flex-wrap items-center gap-2"><span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold capitalize {{ $statusStyle }}">{{ str_replace('_', ' ', $milestone->status) }}</span>@if($milestone->due_at)<span class="text-xs text-gray-500">Due {{ $milestone->due_at->format('M j, Y') }}</span>@endif</div><h3 class="text-base font-semibold text-white">{{ $milestone->title }}</h3>@if($milestone->description)<p class="mt-2 text-sm leading-6 text-gray-400">{{ $milestone->description }}</p>@endif</div>
                                        <p class="text-lg font-bold text-white">₦{{ number_format((float) $milestone->amount, 2) }}</p>
                                    </div>

                                    @if($milestone->revision_message)<div class="mt-4 rounded-xl border border-orange-500/20 bg-orange-500/5 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-orange-300">Revision request</p><p class="mt-2 whitespace-pre-line text-sm text-gray-300">{{ $milestone->revision_message }}</p></div>@endif

                                    @if($milestone->submission_message)<div class="mt-4 rounded-xl border border-dark-700 bg-dark-950 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Latest submission</p><p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-300">{{ $milestone->submission_message }}</p>@if(!empty($milestone->submission_files))<div class="mt-3 flex flex-wrap gap-2">@foreach($milestone->submission_files as $index => $file)<a href="{{ route('contracts.milestones.download', [$contract, $milestone, $index]) }}" class="marketplace-pill hover:border-indigo-500/40 hover:text-indigo-300"><i class="fas fa-paperclip mr-1"></i>{{ $file['name'] ?? 'Attachment' }}</a>@endforeach</div>@endif</div>@endif

                                    <div class="mt-5 border-t border-dark-700 pt-5">
                                        @if($isClient && $milestone->status === 'pending_funding')
                                            <form method="POST" action="{{ route('contracts.milestones.fund', [$contract, $milestone]) }}">@csrf<button class="marketplace-btn-primary"><i class="fas fa-shield-halved"></i>Fund ₦{{ number_format((float) $milestone->amount, 2) }} in escrow</button></form>
                                        @elseif($isFreelancer && in_array($milestone->status, ['funded','revision_requested']))
                                            <form method="POST" action="{{ route('contracts.milestones.start', [$contract, $milestone]) }}">@csrf<button class="marketplace-btn-primary"><i class="fas fa-play"></i>Start milestone</button></form>
                                        @endif

                                        @if($isFreelancer && in_array($milestone->status, ['funded','in_progress','revision_requested']))
                                            <form method="POST" action="{{ route('contracts.milestones.submit', [$contract, $milestone]) }}" enctype="multipart/form-data" class="mt-4 space-y-3">@csrf<label class="marketplace-label">Submit work</label><textarea name="submission_message" rows="4" class="marketplace-input" required placeholder="Explain what was completed and anything the client should review."></textarea><input type="file" name="files[]" multiple class="block w-full text-sm text-gray-400 file:mr-3 file:rounded-lg file:border-0 file:bg-dark-800 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-gray-200"><p class="text-xs text-gray-600">Up to 5 files, 10 MB each. Common document, image and ZIP formats are accepted.</p><button class="marketplace-btn-primary"><i class="fas fa-paper-plane"></i>Submit for review</button></form>
                                        @endif

                                        @if($isClient && $milestone->status === 'submitted')
                                            <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                                                <form method="POST" action="{{ route('contracts.milestones.approve', [$contract, $milestone]) }}">@csrf<button class="marketplace-btn-primary"><i class="fas fa-check"></i>Approve & release payment</button></form>
                                                <form method="POST" action="{{ route('contracts.milestones.revision', [$contract, $milestone]) }}" class="flex-1">@csrf<div class="flex gap-2"><input name="revision_message" class="marketplace-input" required placeholder="Describe the requested changes"><button class="marketplace-btn-secondary whitespace-nowrap">Request revision</button></div></form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="marketplace-card p-8 text-center text-sm text-gray-500">No milestones have been added yet.</div>
                        @endforelse
                    </div>
                </section>

                @if($isClient && $contract->status === 'active')
                    <section class="marketplace-card p-6"><h2 class="text-lg font-semibold text-white">Add another milestone</h2><p class="mt-1 text-sm text-gray-500">Only fund a milestone when its scope and amount are agreed with the freelancer.</p><form method="POST" action="{{ route('contracts.milestones.store', $contract) }}" class="mt-5 grid gap-4 md:grid-cols-2">@csrf<div class="md:col-span-2"><label class="marketplace-label">Milestone title</label><input name="title" class="marketplace-input" required maxlength="160"></div><div><label class="marketplace-label">Amount (₦)</label><input type="number" name="amount" min="1" step="0.01" class="marketplace-input" required></div><div><label class="marketplace-label">Due date</label><input type="date" name="due_at" class="marketplace-input"></div><div class="md:col-span-2"><label class="marketplace-label">Scope</label><textarea name="description" rows="3" class="marketplace-input"></textarea></div><div class="md:col-span-2"><button class="marketplace-btn-secondary"><i class="fas fa-plus"></i>Add milestone</button></div></form></section>
                @endif
            </div>

            <aside class="space-y-5">
                <section class="marketplace-card p-5"><h2 class="text-sm font-semibold text-white">Contract details</h2><dl class="mt-4 space-y-4 text-sm"><div class="flex justify-between gap-4"><dt class="text-gray-500">Type</dt><dd class="font-medium capitalize text-gray-200">{{ $contract->contract_type }}</dd></div><div class="flex justify-between gap-4"><dt class="text-gray-500">Started</dt><dd class="text-gray-300">{{ optional($contract->started_at)->format('M j, Y') ?? '—' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-gray-500">Client</dt><dd class="text-right text-gray-300">{{ $contract->client->name ?? '—' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-gray-500">Freelancer</dt><dd class="text-right text-gray-300">{{ $contract->freelancer->name ?? '—' }}</dd></div></dl></section>
                <section class="marketplace-card p-5"><h2 class="text-sm font-semibold text-white">Payment protection</h2><p class="mt-2 text-sm leading-6 text-gray-500">A funded milestone moves the agreed amount into SwiftKudi escrow. The client releases it after approving submitted work.</p></section>
                @if($contract->job)<a href="{{ route('jobs.show', $contract->job) }}" class="marketplace-btn-secondary w-full"><i class="fas fa-briefcase"></i>View original job</a>@endif
                <a href="{{ route('chat.index') }}" class="marketplace-btn-secondary w-full"><i class="far fa-comment"></i>Open messages</a>
            </aside>
        </div>
    </div>
</div>
@endsection
