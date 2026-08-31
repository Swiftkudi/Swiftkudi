@extends('layouts.app')

@php
    $plainDescription = trim(preg_replace('/\s+/', ' ', strip_tags($service->description)) ?? '');
    $seller = $service->seller;
    $sellerProfile = optional($seller)->freelancerProfile;
    $sellerProfileUrl = $sellerProfile && $sellerProfile->slug
        ? route('freelancers.show', $sellerProfile->slug)
        : ($seller ? route('professional-services.provider-profile', $seller->id) : null);
    $portfolioReferences = collect($service->portfolio_links ?? [])->filter(function ($link) {
        if (!is_string($link) || !filter_var($link, FILTER_VALIDATE_URL)) return false;
        return in_array(strtolower((string) parse_url($link, PHP_URL_SCHEME)), ['http', 'https'], true);
    })->values();
    $serviceSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $service->title,
        'description' => $plainDescription,
        'url' => route('professional-services.show', $service),
        'provider' => [
            '@type' => 'Person',
            'name' => $seller->name ?? 'Service provider',
        ],
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'NGN',
            'price' => (float) $service->price,
            'availability' => $service->status === 'active' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => route('professional-services.show', $service),
        ],
    ];
@endphp

@section('title', $service->title . ' | Professional Service | ' . config('app.name', 'SwiftKudi'))
@section('meta_description', \Illuminate\Support\Str::limit($plainDescription, 155))
@section('canonical', route('professional-services.show', $service))
@section('robots', $service->status === 'active' ? 'index,follow' : 'noindex,follow')
@section('og_title', $service->title . ' | ' . config('app.name', 'SwiftKudi'))
@section('og_description', \Illuminate\Support\Str::limit($plainDescription, 180))

@push('meta')
<script type="application/ld+json">{!! json_encode($serviceSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <nav class="mb-6 flex min-w-0 flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('professional-services.index') }}" class="hover:text-indigo-300">Services</a>
            <i class="fas fa-chevron-right text-[9px]"></i>
            @if($service->category)
                <a href="{{ route('professional-services.index', ['category' => $service->category->slug]) }}" class="hover:text-indigo-300">{{ $service->category->name }}</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
            @endif
            <span class="max-w-[min(34rem,70vw)] truncate text-gray-400">{{ $service->title }}</span>
        </nav>

        <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_350px]">
            <main class="min-w-0 space-y-6 !pt-0">
                <section class="marketplace-card p-5 sm:p-7">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        @if($service->category)<span class="marketplace-pill">{{ $service->category->name }}</span>@endif
                        @if($service->is_featured)<span class="marketplace-status marketplace-status-info"><i class="fas fa-star mr-1"></i>Featured</span>@endif
                        <span class="marketplace-status {{ $service->status === 'active' ? 'marketplace-status-success' : 'marketplace-status-warning' }}">{{ ucfirst($service->status) }}</span>
                    </div>
                    <h1 class="mt-4 max-w-4xl font-heading text-2xl font-bold leading-tight text-white sm:text-3xl lg:text-4xl">{{ $service->title }}</h1>
                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="marketplace-option-card"><p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Starting price</p><p class="mt-2 text-xl font-bold text-white">₦{{ number_format((float)$service->price, 0) }}</p></div>
                        <div class="marketplace-option-card"><p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Delivery</p><p class="mt-2 text-xl font-bold text-white">{{ $service->delivery_days }} day{{ $service->delivery_days == 1 ? '' : 's' }}</p></div>
                        <div class="marketplace-option-card"><p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Revisions</p><p class="mt-2 text-xl font-bold text-white">{{ $service->revisions_included }}</p></div>
                    </div>
                </section>

                <section class="marketplace-card p-5 sm:p-7">
                    <span class="marketplace-eyebrow">What you are buying</span>
                    <h2 class="mt-2 text-xl font-semibold text-white">Service scope</h2>
                    <div class="mt-4 whitespace-pre-line text-[15px] leading-7 text-gray-400">{{ $service->description }}</div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-dark-700 bg-dark-950 p-4"><span class="marketplace-icon-box"><i class="far fa-clock"></i></span><h3 class="mt-3 text-sm font-semibold text-white">Delivery target</h3><p class="mt-1 text-sm leading-6 text-gray-500">The seller has listed {{ $service->delivery_days }} day{{ $service->delivery_days == 1 ? '' : 's' }} for this service.</p></div>
                        <div class="rounded-xl border border-dark-700 bg-dark-950 p-4"><span class="marketplace-icon-box"><i class="fas fa-rotate-left"></i></span><h3 class="mt-3 text-sm font-semibold text-white">Revision allowance</h3><p class="mt-1 text-sm leading-6 text-gray-500">{{ $service->revisions_included }} revision{{ $service->revisions_included == 1 ? '' : 's' }} are recorded in the listing terms.</p></div>
                        <div class="rounded-xl border border-dark-700 bg-dark-950 p-4"><span class="marketplace-icon-box"><i class="fas fa-shield-halved"></i></span><h3 class="mt-3 text-sm font-semibold text-white">Marketplace record</h3><p class="mt-1 text-sm leading-6 text-gray-500">Requirements, delivery, revisions and payment state remain attached to the order workflow.</p></div>
                    </div>
                </section>

                @if($service->addons && $service->addons->count() > 0)
                    <section class="marketplace-card overflow-hidden">
                        <div class="border-b border-dark-700 px-5 py-4 sm:px-6"><h2 class="text-lg font-semibold text-white">Optional add-ons</h2><p class="mt-1 text-sm text-gray-500">Extras are optional and added to the order total only when selected.</p></div>
                        @foreach($service->addons as $addon)
                            <div class="marketplace-list-row">
                                <span class="marketplace-icon-box flex-none"><i class="fas fa-plus"></i></span>
                                <div class="min-w-0 flex-1"><div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between"><div><h3 class="font-semibold text-white">{{ $addon->name }}</h3>@if($addon->description)<p class="mt-1 text-sm leading-6 text-gray-500">{{ $addon->description }}</p>@endif</div><span class="font-semibold text-white">+₦{{ number_format((float)$addon->price, 0) }}</span></div>@if($addon->delivery_days_extra)<p class="mt-2 text-xs text-gray-600">Adds {{ $addon->delivery_days_extra }} delivery day{{ $addon->delivery_days_extra == 1 ? '' : 's' }}</p>@endif</div>
                            </div>
                        @endforeach
                    </section>
                @endif

                @if($portfolioReferences->isNotEmpty())
                    <section class="marketplace-card p-5 sm:p-6">
                        <h2 class="text-lg font-semibold text-white">Portfolio references</h2>
                        <p class="mt-1 text-sm text-gray-500">External examples supplied with this listing. They are not SwiftKudi verification claims.</p>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach($portfolioReferences as $link)
                                <a href="{{ $link }}" rel="nofollow noopener noreferrer" target="_blank" class="flex min-w-0 items-center justify-between gap-3 rounded-lg border border-dark-700 bg-dark-950 px-4 py-3 text-sm text-gray-300 hover:border-indigo-500/40 hover:text-indigo-300"><span class="truncate">{{ parse_url($link, PHP_URL_HOST) ?: $link }}</span><i class="fas fa-arrow-up-right-from-square flex-none text-xs"></i></a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="marketplace-card overflow-hidden">
                    <div class="flex flex-col gap-3 border-b border-dark-700 px-5 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                        <div><h2 class="text-lg font-semibold text-white">Client feedback</h2><p class="mt-1 text-sm text-gray-500">Reviews tied to completed orders for this service.</p></div>
                        @if($reviews->isNotEmpty())<p class="text-sm text-gray-400"><i class="fas fa-star mr-1 text-amber-400"></i>{{ number_format((float)$reviews->avg('rating'), 1) }} from {{ $reviews->count() }} shown</p>@endif
                    </div>
                    @forelse($reviews as $review)
                        <article class="marketplace-list-row">
                            <span class="marketplace-avatar flex-none">{{ strtoupper(substr(optional($review->reviewer)->name ?: 'C', 0, 2)) }}</span>
                            <div class="min-w-0 flex-1"><div class="flex flex-wrap items-center justify-between gap-2"><div><p class="font-semibold text-white">{{ optional($review->reviewer)->name ?: 'Client' }}</p><p class="mt-1 text-xs text-gray-600">{{ optional($review->created_at)->format('M Y') }}</p></div><span class="text-sm font-semibold text-gray-200"><i class="fas fa-star mr-1 text-amber-400"></i>{{ (int)$review->rating }}/5</span></div><p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-400">{{ $review->comment }}</p>@if($review->response)<div class="mt-3 rounded-lg border border-dark-700 bg-dark-950 p-3 text-sm leading-6 text-gray-500"><strong class="text-gray-300">Seller response:</strong> {{ $review->response }}</div>@endif</div>
                        </article>
                    @empty
                        <div class="p-7 text-center text-sm text-gray-500">This service has no completed-order reviews yet.</div>
                    @endforelse
                </section>

                <section class="marketplace-card p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-white">How the order works</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach([
                            ['1','Share requirements','Describe the work and select any optional add-ons.'],
                            ['2','Order is recorded','The marketplace records the order and applicable escrow/payment state.'],
                            ['3','Review delivery','The seller delivers through the order workflow and revisions can be requested where allowed.'],
                            ['4','Complete the work','Approved work and eligible feedback remain in marketplace history.'],
                        ] as $step)
                            <div><span class="flex h-8 w-8 items-center justify-center rounded-full border border-indigo-500/30 bg-indigo-500/10 text-xs font-bold text-indigo-300">{{ $step[0] }}</span><h3 class="mt-3 text-sm font-semibold text-white">{{ $step[1] }}</h3><p class="mt-1 text-sm leading-6 text-gray-500">{{ $step[2] }}</p></div>
                        @endforeach
                    </div>
                </section>
            </main>

            <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
                <section class="marketplace-card p-5 sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Starting at</p>
                    <p class="mt-1 text-3xl font-bold text-white">₦{{ number_format((float)$service->price, 0) }}</p>
                    <div class="mt-4 space-y-2 text-sm text-gray-400"><div class="flex justify-between gap-3"><span>Delivery</span><strong class="text-gray-200">{{ $service->delivery_days }} day{{ $service->delivery_days == 1 ? '' : 's' }}</strong></div><div class="flex justify-between gap-3"><span>Revisions</span><strong class="text-gray-200">{{ $service->revisions_included }}</strong></div>@if($service->addons && $service->addons->count())<div class="flex justify-between gap-3"><span>Optional add-ons</span><strong class="text-gray-200">{{ $service->addons->count() }}</strong></div>@endif</div>

                    @if($service->status === 'active')
                        @auth
                            @if(auth()->id() === $service->user_id)
                                <a href="{{ route('professional-services.my-services') }}" class="marketplace-btn-primary mt-5 w-full"><i class="fas fa-pen"></i>Manage this service</a>
                            @else
                                <button type="button" onclick="showOrderModal()" class="marketplace-btn-primary mt-5 w-full"><i class="fas fa-cart-shopping"></i>Order service</button>
                                <button type="button" onclick="showContactModal()" class="marketplace-btn-secondary mt-2 w-full"><i class="far fa-comment"></i>Message seller</button>
                                <a href="{{ route('chat.open', ['type' => 'professional_service', 'referenceId' => $service->id, 'participantId' => $service->user_id]) }}" class="marketplace-btn-secondary mt-2 w-full"><i class="fas fa-comments"></i>Open chat</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="marketplace-btn-primary mt-5 w-full">Log in to order</a>
                            <p class="mt-2 text-center text-xs text-gray-600">Sign in before ordering or messaging a provider.</p>
                        @endauth
                    @else
                        <div class="mt-5 rounded-lg border border-dark-700 bg-dark-950 p-3 text-center text-sm text-gray-500">This service is currently unavailable for new orders.</div>
                    @endif
                    @if($userHasOrder)<a href="{{ route('professional-services.orders.index') }}" class="mt-4 block text-center text-xs font-semibold text-indigo-300">View your existing order history</a>@endif
                </section>

                <section class="marketplace-card p-5 sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Service provider</p>
                    <div class="mt-4 flex items-start gap-3"><span class="marketplace-avatar marketplace-avatar-lg">{{ strtoupper(substr(optional($seller)->name ?: 'S',0,2)) }}</span><div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-2"><h2 class="truncate font-semibold text-white">{{ optional($seller)->name ?: 'Service provider' }}</h2>@if(optional($seller)->marketplace_seller_verified)<i class="fas fa-circle-check text-xs text-indigo-300" title="Verified marketplace seller" aria-label="Verified marketplace seller"></i>@endif</div><p class="mt-1 line-clamp-2 text-sm text-gray-500">{{ optional($sellerProfile)->professional_title ?: 'Independent professional' }}</p></div></div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-sm"><div class="rounded-lg border border-dark-700 bg-dark-950 p-3"><p class="text-xs text-gray-600">Rating</p>@if((optional($seller)->seller_rating_count ?? 0) > 0)<p class="mt-1 font-semibold text-white"><i class="fas fa-star mr-1 text-amber-400"></i>{{ number_format((float)$seller->seller_rating, 1) }}</p><p class="mt-1 text-[11px] text-gray-600">{{ $seller->seller_rating_count }} review{{ $seller->seller_rating_count === 1 ? '' : 's' }}</p>@else<p class="mt-1 text-sm font-semibold text-gray-300">No reviews yet</p>@endif</div><div class="rounded-lg border border-dark-700 bg-dark-950 p-3"><p class="text-xs text-gray-600">Completed</p><p class="mt-1 font-semibold text-white">{{ number_format((int)(optional($sellerProfile)->total_orders_completed ?? 0)) }}</p><p class="mt-1 text-[11px] text-gray-600">service order{{ (optional($sellerProfile)->total_orders_completed ?? 0) === 1 ? '' : 's' }}</p></div></div>
                    @if($sellerProfileUrl)<a href="{{ $sellerProfileUrl }}" class="marketplace-btn-secondary mt-4 w-full">View freelancer profile</a>@endif
                </section>
            </aside>
        </div>
    </div>
</div>

@if($service->status === 'active')
<div id="order-modal" class="fixed inset-0 z-[300] hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
    <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-2xl border border-dark-700 bg-dark-900 shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-dark-700 px-5 py-4 sm:px-6"><div><h2 id="order-modal-title" class="text-lg font-semibold text-white">Order service</h2><p class="mt-1 line-clamp-1 text-sm text-gray-500">{{ $service->title }}</p></div><button type="button" onclick="hideOrderModal()" class="rounded-lg p-2 text-gray-500 hover:bg-dark-800 hover:text-white" aria-label="Close order form"><i class="fas fa-times"></i></button></div>
        <form id="order-form" class="space-y-5 p-5 sm:p-6">
            @csrf
            <div id="service-order-error-box" class="hidden rounded-lg border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300"></div>
            <div><label for="service-requirements" class="marketplace-label">Your requirements</label><textarea id="service-requirements" name="requirements" rows="5" minlength="10" maxlength="5000" class="marketplace-input resize-y" placeholder="Describe the expected outcome, files, dimensions, audience, deadline or other details the seller needs." required></textarea><p class="mt-1.5 text-xs text-gray-600">Be specific so the seller can start from a clear brief.</p></div>
            @if($service->addons && $service->addons->count() > 0)
                <fieldset><legend class="marketplace-label">Optional add-ons</legend><div class="space-y-2">@foreach($service->addons as $addon)<label class="flex cursor-pointer items-start gap-3 rounded-lg border border-dark-700 bg-dark-950 p-3 hover:border-indigo-500/40"><input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" class="addon-checkbox marketplace-checkbox mt-1 h-4 w-4" data-price="{{ $addon->price }}"><span class="min-w-0 flex-1"><span class="flex items-start justify-between gap-3"><strong class="text-sm text-gray-200">{{ $addon->name }}</strong><span class="whitespace-nowrap text-sm font-semibold text-white">+₦{{ number_format((float)$addon->price,0) }}</span></span>@if($addon->description)<span class="mt-1 block text-xs leading-5 text-gray-600">{{ $addon->description }}</span>@endif</span></label>@endforeach</div></fieldset>
            @endif
            <div class="rounded-xl border border-dark-700 bg-dark-950 p-4"><div class="flex justify-between text-sm text-gray-500"><span>Service</span><span>₦{{ number_format((float)$service->price,0) }}</span></div><div class="mt-2 flex justify-between text-sm text-gray-500"><span>Add-ons</span><span id="addons-total">₦0</span></div><div class="mt-3 flex justify-between border-t border-dark-700 pt-3 text-base font-semibold text-white"><span>Total</span><span id="order-total">₦{{ number_format((float)$service->price,0) }}</span></div></div>
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" onclick="hideOrderModal()" class="marketplace-btn-secondary">Cancel</button><button id="service-order-submit" type="submit" class="marketplace-btn-primary"><i class="fas fa-lock"></i>Place order</button></div>
        </form>
    </div>
</div>
@endif

@auth
@if(auth()->id() !== $service->user_id)
<div id="contact-modal" class="fixed inset-0 z-[300] hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="contact-modal-title">
    <div class="w-full max-w-lg rounded-2xl border border-dark-700 bg-dark-900 shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-dark-700 px-5 py-4 sm:px-6"><div><h2 id="contact-modal-title" class="text-lg font-semibold text-white">Message {{ optional($seller)->name ?: 'seller' }}</h2><p class="mt-1 text-sm text-gray-500">Ask a project question before ordering.</p></div><button type="button" onclick="hideContactModal()" class="rounded-lg p-2 text-gray-500 hover:bg-dark-800 hover:text-white" aria-label="Close message form"><i class="fas fa-times"></i></button></div>
        <form id="contact-form" class="space-y-4 p-5 sm:p-6">
            @csrf
            <input type="hidden" name="recipient_id" value="{{ $service->user_id }}">
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            <div id="service-contact-error-box" class="hidden rounded-lg border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300"></div>
            <div><label for="service-contact-subject" class="marketplace-label">Subject</label><input id="service-contact-subject" type="text" name="subject" minlength="3" maxlength="255" class="marketplace-input" placeholder="Project question" required></div>
            <div><label for="service-contact-message" class="marketplace-label">Message</label><textarea id="service-contact-message" name="message" rows="5" minlength="10" maxlength="5000" class="marketplace-input resize-y" placeholder="Explain what you need and what you would like clarified." required></textarea></div>
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" onclick="hideContactModal()" class="marketplace-btn-secondary">Cancel</button><button id="service-contact-submit" type="submit" class="marketplace-btn-primary"><i class="fas fa-paper-plane"></i>Send message</button></div>
        </form>
    </div>
</div>
@endif
@endauth

<div id="service-show-config" data-order-url="{{ route('professional-services.order', $service) }}" data-contact-url="{{ route('professional-services.contact') }}" data-base-price="{{ $service->price }}"></div>

@push('scripts')
<script>
(() => {
    const serviceConfig = document.getElementById('service-show-config');
    const orderModal = document.getElementById('order-modal');
    const contactModal = document.getElementById('contact-modal');
    const orderForm = document.getElementById('order-form');
    const contactForm = document.getElementById('contact-form');
    const orderUrl = serviceConfig?.dataset?.orderUrl || '';
    const contactUrl = serviceConfig?.dataset?.contactUrl || '';
    const basePrice = Number(serviceConfig?.dataset?.basePrice || 0);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const openModal = modal => { if (!modal) return; modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow='hidden'; };
    const closeModal = modal => { if (!modal) return; modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.style.overflow=''; };
    window.showOrderModal = () => openModal(orderModal);
    window.hideOrderModal = () => closeModal(orderModal);
    window.showContactModal = () => openModal(contactModal);
    window.hideContactModal = () => closeModal(contactModal);

    const addonCheckboxes = Array.from(document.querySelectorAll('.addon-checkbox'));
    const updateTotal = () => {
        const addonTotal = addonCheckboxes.reduce((sum, checkbox) => checkbox.checked ? sum + Number(checkbox.dataset.price || 0) : sum, 0);
        const addonNode = document.getElementById('addons-total');
        const totalNode = document.getElementById('order-total');
        if (addonNode) addonNode.textContent = '₦' + addonTotal.toLocaleString();
        if (totalNode) totalNode.textContent = '₦' + (basePrice + addonTotal).toLocaleString();
    };
    addonCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updateTotal));

    const showFormError = (form, boxId, data, fallback) => {
        if (window.SwiftkudiFormFeedback) {
            window.SwiftkudiFormFeedback.showValidationErrors(form, data || {message:fallback}, {boxId});
            return;
        }
        const box=document.getElementById(boxId); if(box){box.textContent=(data?.message||fallback);box.classList.remove('hidden');}
    };

    orderForm?.addEventListener('submit', async event => {
        event.preventDefault();
        const submit = document.getElementById('service-order-submit');
        if (submit) submit.disabled = true;
        try {
            const response = await fetch(orderUrl, {method:'POST',body:new FormData(orderForm),headers:{'X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
            const data = await response.json().catch(() => ({success:false,message:'The server returned an unexpected response.'}));
            if (data.redirect) { window.location.href=data.redirect; return; }
            if (!response.ok || !data.success) { showFormError(orderForm,'service-order-error-box',data,'The order could not be placed. Please review the form and retry.'); return; }
            closeModal(orderModal);
            if (data.redirect) window.location.href=data.redirect;
            else window.location.href='{{ route('professional-services.orders.index') }}';
        } catch (_) {
            showFormError(orderForm,'service-order-error-box',null,'The order could not be placed. Check your connection and retry.');
        } finally { if (submit) submit.disabled=false; }
    });

    contactForm?.addEventListener('submit', async event => {
        event.preventDefault();
        const submit = document.getElementById('service-contact-submit');
        if (submit) submit.disabled=true;
        try {
            const response = await fetch(contactUrl,{method:'POST',body:new FormData(contactForm),headers:{'X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
            const data = await response.json().catch(() => ({success:false,message:'The server returned an unexpected response.'}));
            if (!response.ok || !data.success) { showFormError(contactForm,'service-contact-error-box',data,'Your message could not be sent. Please retry.'); return; }
            contactForm.reset(); closeModal(contactModal);
            if (data.chat_url) window.location.href=data.chat_url;
        } catch (_) {
            showFormError(contactForm,'service-contact-error-box',null,'Your message could not be sent. Check your connection and retry.');
        } finally { if (submit) submit.disabled=false; }
    });

    document.addEventListener('keydown', event => { if(event.key==='Escape'){ closeModal(orderModal); closeModal(contactModal); } });
    [orderModal, contactModal].forEach(modal => modal?.addEventListener('click', event => { if(event.target===modal) closeModal(modal); }));
})();
</script>
@endpush
@endsection
