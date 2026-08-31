@extends('layouts.app')

@section('title', 'Service Order #' . $order->id . ' - SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
@php
    $isSeller = auth()->id() === $order->seller_id;
    $isBuyer = auth()->id() === $order->buyer_id;
    $otherUser = $isSeller ? $order->buyer : $order->seller;
    $backRoute = $isSeller ? 'professional-services.sales.index' : 'professional-services.orders.index';
    $backLabel = $isSeller ? 'Back to sales' : 'Back to purchases';
    $statusClass = match($order->status) {
        'completed' => 'border-emerald-500/25 bg-emerald-500/10 text-emerald-300',
        'delivered' => 'border-indigo-500/25 bg-indigo-500/10 text-indigo-300',
        'revision' => 'border-amber-500/25 bg-amber-500/10 text-amber-300',
        'cancelled', 'refunded' => 'border-red-500/25 bg-red-500/10 text-red-300',
        default => 'border-slate-700 bg-slate-800 text-slate-300',
    };
@endphp

<div class="marketplace-page">
    <div class="marketplace-container max-w-6xl">
        <div class="mb-5">
            <a href="{{ route($backRoute) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-300 hover:text-indigo-200"><i class="fas fa-arrow-left"></i>{{ $backLabel }}</a>
        </div>

        <div class="marketplace-page-header">
            <div>
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $statusClass }}">{{ str_replace('_', ' ', $order->status) }}</span>
                    <span class="text-xs text-slate-500">Order #{{ $order->id }} · Created {{ $order->created_at->format('M j, Y') }}</span>
                </div>
                <h1 class="marketplace-title">{{ $order->service->title ?? 'Professional service order' }}</h1>
                <p class="marketplace-subtitle">{{ $isSeller ? 'Manage delivery and client feedback from one workspace.' : 'Track delivery, revisions and escrow-backed completion from one workspace.' }}</p>
            </div>
            @if($otherUser)
                <a href="{{ route('chat.open', ['type' => 'professional_service', 'referenceId' => $order->service_id, 'participantId' => $otherUser->id]) }}" class="marketplace-btn-primary"><i class="far fa-comment"></i>Message {{ $isSeller ? 'client' : 'seller' }}</a>
            @endif
        </div>

        <div id="order-action-feedback" class="hidden marketplace-card mb-5 border-red-500/40 bg-red-500/5 p-4 text-sm text-red-200" role="alert" aria-live="polite"></div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
            <div class="space-y-5">
                <section class="marketplace-card p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-white">Order overview</h2>
                    <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $isSeller ? 'Client' : 'Seller' }}</dt><dd class="mt-1 font-medium text-slate-200">{{ $otherUser->name ?? 'User' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Delivery promise</dt><dd class="mt-1 font-medium text-slate-200">{{ $order->service ? $order->service->delivery_days . ' days' : 'Not available' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Revisions requested</dt><dd class="mt-1 font-medium text-slate-200">{{ (int) ($order->revisions_requested ?? 0) }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Last updated</dt><dd class="mt-1 font-medium text-slate-200">{{ $order->updated_at->diffForHumans() }}</dd></div>
                    </dl>
                    @if($order->service?->description)
                        <div class="mt-5 border-t border-slate-800 pt-5"><h3 class="text-sm font-semibold text-slate-200">Service scope</h3><p class="mt-2 text-sm leading-6 text-slate-400">{{ $order->service->description }}</p></div>
                    @endif
                </section>

                @if($order->requirements)
                    <section class="marketplace-card p-5 sm:p-6">
                        <h2 class="text-lg font-semibold text-white">Client requirements</h2>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-400">{{ $order->requirements }}</p>
                    </section>
                @endif

                @if($order->revision_notes)
                    <section class="marketplace-card border-amber-500/25 p-5 sm:p-6">
                        <div class="flex items-center gap-2"><i class="fas fa-rotate-left text-amber-300"></i><h2 class="text-lg font-semibold text-white">Revision request</h2></div>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-400">{{ $order->revision_notes }}</p>
                        <p class="mt-3 text-xs text-amber-300">Revision request #{{ (int) ($order->revisions_requested ?? 0) }}</p>
                    </section>
                @endif

                @if($order->delivery_notes)
                    <section class="marketplace-card border-indigo-500/25 p-5 sm:p-6">
                        <div class="flex items-center gap-2"><i class="fas fa-box-open text-indigo-300"></i><h2 class="text-lg font-semibold text-white">Latest delivery</h2></div>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-400">{{ $order->delivery_notes }}</p>
                        @if($order->delivered_at)<p class="mt-3 text-xs text-slate-500">Delivered {{ $order->delivered_at->diffForHumans() }}</p>@endif
                    </section>
                @endif

                @if($order->messages && $order->messages->isNotEmpty())
                    <section class="marketplace-card p-5 sm:p-6">
                        <div class="flex items-center justify-between gap-3"><div><h2 class="text-lg font-semibold text-white">Order notes</h2><p class="mt-1 text-sm text-slate-500">Earlier service-order messages preserved for this order.</p></div>@if($otherUser)<a href="{{ route('chat.open', ['type' => 'professional_service', 'referenceId' => $order->service_id, 'participantId' => $otherUser->id]) }}" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200">Open Messages</a>@endif</div>
                        <div class="mt-5 max-h-96 space-y-3 overflow-y-auto pr-1">
                            @foreach($order->messages as $message)
                                <div class="rounded-xl border p-4 {{ $message->sender_id === auth()->id() ? 'border-indigo-500/25 bg-indigo-500/10' : 'border-slate-800 bg-slate-950/30' }}">
                                    <div class="flex items-center justify-between gap-3"><span class="text-xs font-semibold text-slate-300">{{ $message->sender->name ?? 'User' }}</span><span class="text-xs text-slate-500">{{ $message->created_at->diffForHumans() }}</span></div>
                                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-300">{{ $message->message }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="space-y-5 lg:sticky lg:top-24">
                <section class="marketplace-card p-5">
                    <h2 class="font-semibold text-white">Payment</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Base service</dt><dd class="font-medium text-slate-200">₦{{ number_format((float) $order->service_price, 2) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Add-ons</dt><dd class="font-medium text-slate-200">₦{{ number_format((float) $order->addons_total, 2) }}</dd></div>
                        <div class="border-t border-slate-800 pt-3 flex justify-between gap-3"><dt class="font-semibold text-slate-200">Order total</dt><dd class="font-semibold text-white">₦{{ number_format((float) $order->total_amount, 2) }}</dd></div>
                        @if($isSeller)
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Platform commission</dt><dd class="font-medium text-slate-300">−₦{{ number_format((float) $order->platform_commission, 2) }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="font-semibold text-slate-200">Seller payout</dt><dd class="font-semibold text-emerald-300">₦{{ number_format((float) $order->seller_payout, 2) }}</dd></div>
                        @else
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Escrow amount</dt><dd class="font-medium text-indigo-300">₦{{ number_format((float) $order->escrow_amount, 2) }}</dd></div>
                        @endif
                    </dl>
                </section>

                @if($isSeller && in_array($order->status, ['paid', 'in_progress'], true))
                    <section id="seller-delivery" class="marketplace-card p-5">
                        <p class="marketplace-eyebrow">Action required</p>
                        <h2 class="mt-1 font-semibold text-white">Submit delivery</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Describe what you completed. The client will be notified and can approve or request a revision.</p>
                        <form action="{{ route('professional-services.orders.deliver', $order) }}" method="POST" class="order-action-form mt-4 space-y-3">
                            @csrf
                            <div><label class="marketplace-label" for="delivery-notes">Delivery notes</label><textarea id="delivery-notes" name="notes" required minlength="10" maxlength="10000" rows="5" class="marketplace-input resize-y" placeholder="Summarize the completed work and any handoff information."></textarea></div>
                            <button type="submit" class="marketplace-btn-primary w-full"><i class="fas fa-paper-plane"></i>Submit delivery</button>
                        </form>
                    </section>
                @endif

                @if($isBuyer && in_array($order->status, ['delivered', 'revision'], true))
                    <section id="buyer-review" class="marketplace-card p-5">
                        <p class="marketplace-eyebrow">Action required</p>
                        <h2 class="mt-1 font-semibold text-white">Review the delivery</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Approving releases escrow and records your review. Request a revision only when changes are genuinely needed.</p>

                        <form action="{{ route('professional-services.orders.approve', $order) }}" method="POST" class="order-action-form mt-4 space-y-3">
                            @csrf
                            <div><label for="delivery-rating" class="marketplace-label">Rating</label><select id="delivery-rating" name="rating" required class="marketplace-input"><option value="">Select rating</option><option value="5">5 — Excellent</option><option value="4">4 — Good</option><option value="3">3 — Okay</option><option value="2">2 — Poor</option><option value="1">1 — Bad</option></select></div>
                            <div><label for="delivery-review" class="marketplace-label">Review</label><textarea id="delivery-review" name="comment" required minlength="10" maxlength="1000" rows="4" class="marketplace-input resize-y" placeholder="Describe your experience with the completed work."></textarea></div>
                            <button type="submit" class="marketplace-btn-primary w-full"><i class="fas fa-check"></i>Approve & release payment</button>
                        </form>

                        @if($order->canRequestRevision())
                            <details class="mt-4 border-t border-slate-800 pt-4">
                                <summary class="cursor-pointer text-sm font-semibold text-amber-300">Request a revision instead</summary>
                                <form action="{{ route('professional-services.orders.revision', $order) }}" method="POST" class="order-action-form mt-3 space-y-3">
                                    @csrf
                                    <div><label for="revision-notes" class="marketplace-label">Revision notes</label><textarea id="revision-notes" name="notes" required minlength="10" maxlength="5000" rows="4" class="marketplace-input resize-y" placeholder="Be specific about what needs to change."></textarea></div>
                                    <button type="submit" class="marketplace-btn-secondary w-full text-amber-200"><i class="fas fa-rotate-left"></i>Request revision</button>
                                </form>
                            </details>
                        @endif
                    </section>
                @endif

                @if($isBuyer && $order->canCancel())
                    <section class="marketplace-card p-5">
                        <h2 class="font-semibold text-white">Cancel order</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Cancellation follows the existing SwiftKudi escrow/refund rules for this order status.</p>
                        <form action="{{ route('professional-services.orders.cancel', $order) }}" method="POST" class="order-action-form mt-4" data-confirm="Cancel this order?">
                            @csrf
                            <button type="submit" class="marketplace-btn-secondary w-full text-red-300"><i class="fas fa-times"></i>Cancel order</button>
                        </form>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const feedback = document.getElementById('order-action-feedback');
    const forms = document.querySelectorAll('.order-action-form');

    function showError(message) {
        feedback.textContent = message;
        feedback.classList.remove('hidden');
        feedback.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    forms.forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            feedback.classList.add('hidden');
            feedback.textContent = '';

            if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) return;
            if (!form.checkValidity()) { form.reportValidity(); return; }

            const button = form.querySelector('button[type="submit"]');
            const original = button ? button.innerHTML : '';
            if (button) { button.disabled = true; button.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>Processing…'; }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: new FormData(form)
                });
                const data = await response.json().catch(() => ({ message: 'Unexpected server response.' }));
                if (response.ok && data.success) { window.location.reload(); return; }

                let message = data.message || 'The action could not be completed. Please try again.';
                if (data.errors) message += ' ' + Object.values(data.errors).flat().join(' ');
                showError(message);
            } catch (error) {
                console.error(error);
                showError('Network error. Please check your connection and try again.');
            } finally {
                if (button) { button.disabled = false; button.innerHTML = original; }
            }
        });
    });
})();
</script>
@endpush
@endsection
