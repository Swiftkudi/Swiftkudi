@extends('layouts.app')

@php
    $plainDescription = trim(preg_replace('/\s+/', ' ', strip_tags($service->description)) ?? '');
    $serviceSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $service->title,
        'description' => $plainDescription,
        'url' => route('professional-services.show', $service),
        'provider' => [
            '@type' => 'Person',
            'name' => $service->seller->name ?? 'Service provider',
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
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm mb-6">
            <a href="{{ route('professional-services.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i class="fas fa-briefcase mr-1"></i> Services
            </a>
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            @if($service->category)
                <a href="{{ route('professional-services.index', ['category' => $service->category->slug]) }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    {{ $service->category->name }}
                </a>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            @endif
            <span class="text-gray-900 dark:text-gray-100 font-medium truncate max-w-[200px]">{{ $service->title }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_360px] gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Hero Section -->
                <div class="relative bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl p-8 overflow-hidden">
                    <!-- Decorative Elements -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                    
                    <div class="relative z-10">
                        <!-- Status Badge -->
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            @if($service->category)
                                <span class="px-3 py-1.5 bg-white/20 backdrop-blur-sm text-white text-sm font-medium rounded-full">
                                    {{ $service->category->name }}
                                </span>
                            @endif
                            @if($service->is_featured)
                                <span class="px-3 py-1.5 bg-yellow-400 text-yellow-900 text-sm font-bold rounded-full flex items-center gap-1">
                                    <i class="fas fa-star"></i> Featured
                                </span>
                            @endif
                            @php
                                $statusClass = $service->status === 'active' ? 'bg-green-400' : 'bg-gray-400';
                            @endphp
                            <span class="px-3 py-1.5 {{ $statusClass }} text-white text-sm font-medium rounded-full">
                                {{ ucfirst($service->status) }}
                            </span>
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ $service->title }}</h1>
                        
                        <!-- Quick Stats -->
                        <div class="grid gap-3 sm:grid-cols-3 text-white/90">
                            <div class="rounded-3xl bg-white/10 p-4">
                                <p class="text-sm uppercase tracking-[0.25em] text-white/70">Delivery</p>
                                <p class="mt-2 font-semibold">{{ $service->delivery_days }} days</p>
                            </div>
                            <div class="rounded-3xl bg-white/10 p-4">
                                <p class="text-sm uppercase tracking-[0.25em] text-white/70">Revisions</p>
                                <p class="mt-2 font-semibold">{{ $service->revisions_included }}</p>
                            </div>
                            <div class="rounded-3xl bg-white/10 p-4">
                                <p class="text-sm uppercase tracking-[0.25em] text-white/70">Rating</p>
                                <p class="mt-2 font-semibold">{{ number_format($service->seller->seller_rating ?? 0, 1) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Why Choose This Service -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <i class="fas fa-bolt text-indigo-500"></i>
                        Service terms
                    </h2>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20">
                            <p class="text-sm text-indigo-700 dark:text-indigo-200 font-semibold mb-2">Delivery target</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">The provider has listed a {{ $service->delivery_days }}-day delivery target for this service.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-purple-50 dark:bg-purple-900/20">
                            <p class="text-sm text-purple-700 dark:text-purple-200 font-semibold mb-2">Included revisions</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">The listing includes {{ $service->revisions_included }} revision{{ $service->revisions_included == 1 ? '' : 's' }} within the agreed service scope.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-green-50 dark:bg-emerald-900/20">
                            <p class="text-sm text-emerald-700 dark:text-emerald-200 font-semibold mb-2">Marketplace workflow</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Requirements, delivery, revisions and payment status stay connected to the marketplace order workflow.</p>
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <i class="fas fa-align-left text-indigo-500"></i>
                        About This Service
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-line">{{ $service->description }}</p>
                </div>

                <!-- Features Card -->
                @if($service->portfolio_links && is_array($service->portfolio_links) && count($service->portfolio_links) > 0)
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <i class="fas fa-check-circle text-green-500"></i>
                        What's Included
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($service->portfolio_links as $feature)
                            <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check text-white text-sm"></i>
                                </div>
                                <span class="text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Add-ons Card -->
                @if($service->addons && $service->addons->count() > 0)
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-purple-500"></i>
                        Optional Add-ons
                    </h2>
                    <div class="space-y-3">
                        @foreach($service->addons as $addon)
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-100 dark:border-purple-800">
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $addon->name }}</h3>
                                    @if($addon->description)
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $addon->description }}</p>
                                    @endif
                                </div>
                                <span class="text-lg font-bold text-purple-600 dark:text-purple-400">+₦{{ number_format($addon->price) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Seller Card -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <i class="fas fa-user-circle text-indigo-500"></i>
                        About the Seller
                    </h2>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold">
                            {{ substr($service->seller->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $service->seller->name ?? 'Unknown' }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Service Provider</p>
                            <div class="mt-3 flex flex-wrap gap-3 text-sm text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-2"><i class="fas fa-star text-yellow-400"></i> {{ number_format($service->seller->seller_rating ?? 0, 1) }}</span>
                                <span>{{ $service->seller->seller_rating_count ?? 0 }} reviews</span>
                                @if($service->seller->marketplace_seller_verified)
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-200">Verified seller</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    <!-- Pricing Card -->
                    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
                        <!-- Price Header -->
                        <div class="bg-indigo-600 p-6 text-white">
                            <div class="text-sm text-indigo-200 mb-1">Starting at</div>
                            <div class="text-4xl font-bold">₦{{ number_format($service->price) }}</div>
                        </div>

                        <!-- Delivery Info -->
                        <div class="p-6 border-b border-gray-100 dark:border-dark-700">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-3 bg-gray-50 dark:bg-dark-800 rounded-xl">
                                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $service->delivery_days }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Days Delivery</div>
                                </div>
                                <div class="text-center p-3 bg-gray-50 dark:bg-dark-800 rounded-xl">
                                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $service->revisions_included }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Revisions</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="p-6">
                            @if($service->status === 'active')
                                <button onclick="showOrderModal()" 
                                        class="w-full py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transform hover:-translate-y-0.5">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Order Now
                                </button>
                            @else
                                <div class="w-full py-4 bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-gray-400 text-center rounded-xl font-medium">
                                    <i class="fas fa-lock mr-2"></i>
                                    Currently unavailable
                                </div>
                            @endif

                            <!-- Contact Seller -->
                            @auth
                                <button onclick="showContactModal()" class="mt-3 w-full py-3 border-2 border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 font-medium rounded-xl flex items-center justify-center gap-2 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                    <i class="fas fa-comment-dots"></i>
                                    Contact seller
                                </button>

                                @if(auth()->id() !== $service->user_id)
                                    <a href="{{ route('chat.open', ['type' => 'professional_service', 'referenceId' => $service->id, 'participantId' => $service->user_id]) }}" class="mt-3 w-full py-3 border-2 border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 font-medium rounded-xl flex items-center justify-center gap-2 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors">
                                        <i class="fas fa-comments"></i>
                                        Open chat
                                    </a>
                                @else
                                    <a href="{{ route('chat.index') }}" class="mt-3 w-full py-3 border-2 border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 font-medium rounded-xl flex items-center justify-center gap-2 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors">
                                        <i class="fas fa-comments"></i>
                                        Open messages
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="mt-3 w-full py-3 border-2 border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 font-medium rounded-xl flex items-center justify-center gap-2 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Login to contact
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Marketplace workflow card -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-200 dark:border-green-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-shield-alt text-white"></i>
                            </div>
                            <h3 class="font-bold text-green-800 dark:text-green-300">Marketplace order workflow</h3>
                        </div>
                        <ul class="space-y-2 text-sm text-green-700 dark:text-green-400">
                            <li class="flex items-center gap-2">
                                <i class="fas fa-check"></i>
                                Wallet / escrow handling where the order workflow applies
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-check"></i>
                                Order history, delivery and revision records
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-check"></i>
                                Dispute workflow available for eligible marketplace orders
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Modal -->
@if($service->status === 'active')
<div id="order-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-dark-900 rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <!-- Modal Header -->
        <div class="bg-indigo-600 p-6 rounded-t-3xl">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">Order Service</h2>
                <button onclick="hideOrderModal()" class="text-white/80 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <p class="text-indigo-200 mt-1">{{ $service->title }}</p>
        </div>

        <!-- Modal Body -->
        <form id="order-form" class="p-6 space-y-6">
            @csrf
            <!-- Requirements -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-clipboard-list mr-2 text-indigo-500"></i>
                    Your Requirements
                </label>
                <textarea name="requirements" rows="4" 
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                          placeholder="Describe what you need in detail..." required></textarea>
            </div>

            <!-- Add-ons -->
            @if($service->addons && $service->addons->count() > 0)
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    <i class="fas fa-plus-circle mr-2 text-purple-500"></i>
                    Optional Add-ons
                </label>
                <div class="space-y-2">
                    @foreach($service->addons as $addon)
                        <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-dark-800 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                            <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" 
                                   class="addon-checkbox w-5 h-5 rounded border-gray-300 dark:border-dark-600 text-indigo-600 focus:ring-indigo-500"
                                   data-price="{{ $addon->price }}">
                            <div class="flex-1">
                                <span class="text-gray-900 dark:text-gray-100">{{ $addon->name }}</span>
                                <span class="text-purple-600 dark:text-purple-400 font-medium ml-2">+₦{{ number_format($addon->price) }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Price Summary -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-dark-800 dark:to-dark-700 rounded-xl p-4 space-y-3">
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Service Price</span>
                    <span>₦{{ number_format($service->price) }}</span>
                </div>
                <div class="flex justify-between text-purple-600 dark:text-purple-400">
                    <span>Add-ons</span>
                    <span id="addons-total">₦0</span>
                </div>
                <div class="border-t border-gray-200 dark:border-dark-600 pt-3 flex justify-between text-lg font-bold text-gray-900 dark:text-gray-100">
                    <span>Total</span>
                    <span id="order-total">₦{{ number_format($service->price) }}</span>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-3">
                <button type="button" onclick="hideOrderModal()" 
                        class="flex-1 py-3 bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-300 dark:hover:bg-dark-600 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-lock mr-2"></i>Pay & Order
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Contact Seller Modal -->
@auth
<div id="contact-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-dark-900 rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <!-- Modal Header -->
        <div class="bg-indigo-600 p-6 rounded-t-3xl">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">Contact Seller</h2>
                <button onclick="hideContactModal()" class="text-white/80 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <p class="text-indigo-200 mt-1">Send a message to {{ $service->seller->name ?? 'the seller' }}</p>
        </div>

        <!-- Modal Body -->
        <form id="contact-form" class="p-6 space-y-6">
            @csrf
            <input type="hidden" name="recipient_id" value="{{ $service->user_id }}">
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Subject
                </label>
                <input type="text" name="subject" 
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="What's this about?" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Message
                </label>
                <textarea name="message" rows="5" 
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                          placeholder="Write your message here..." required></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="hideContactModal()" 
                        class="flex-1 py-3 bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-300 dark:hover:bg-dark-600 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-paper-plane mr-2"></i>Send
                </button>
            </div>
        </form>
    </div>
</div>
@endauth

<div
    id="service-show-config"
    data-order-url="{{ route('professional-services.order', $service) }}"
    data-contact-url="{{ route('professional-services.contact') }}"
    data-base-price="{{ $service->price }}"
></div>

@push('scripts')
<script>
    const serviceConfig = document.getElementById('service-show-config');
    const orderUrl = serviceConfig?.dataset?.orderUrl || '';
    const contactUrl = serviceConfig?.dataset?.contactUrl || '';
    const basePrice = Number(serviceConfig?.dataset?.basePrice || 0);
    const canContact = !!document.getElementById('contact-modal');

    function showOrderModal() {
        document.getElementById('order-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideOrderModal() {
        document.getElementById('order-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function showContactModal() {
        const modal = document.getElementById('contact-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideContactModal() {
        const modal = document.getElementById('contact-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Calculate add-ons total
    const addonCheckboxes = document.querySelectorAll('.addon-checkbox');

    addonCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotal);
    });

    function updateTotal() {
        let addonsTotal = 0;
        addonCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                addonsTotal += parseInt(checkbox.dataset.price);
            }
        });
        
        document.getElementById('addons-total').textContent = '₦' + addonsTotal.toLocaleString();
        document.getElementById('order-total').textContent = '₦' + (basePrice + addonsTotal).toLocaleString();
    }

    // Handle order form submission
    document.getElementById('order-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        
        fetch(orderUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            }
            // Handle non-JSON responses (like redirects/HTML error pages)
            return response.text().then(text => {
                throw new Error('Unexpected response from server: ' + text.substring(0, 200));
            });
        })
        .then(data => {
            if (!data.success && data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            if (data.redirect) {
                window.location.href = data.redirect;
            } else if (data.message) {
                if (!data.success && (data.errors || data.error_list) && window.SwiftkudiFormFeedback) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, data, {
                        boxId: 'service-order-error-box',
                    });
                    return;
                }
                alert(data.message);
                if (data.success) {
                    hideOrderModal();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.SwiftkudiFormFeedback) {
                window.SwiftkudiFormFeedback.showValidationErrors(form, {
                    message: 'An error occurred while placing your order. Please try again.',
                }, {
                    boxId: 'service-order-error-box',
                });
            } else {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Handle contact form submission
    if (canContact) {
    document.getElementById('contact-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        
        fetch(contactUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            }
            return response.text().then(text => {
                throw new Error('Unexpected response: ' + text.substring(0, 200));
            });
        })
        .then(data => {
            if (!data.success && (data.errors || data.error_list) && window.SwiftkudiFormFeedback) {
                window.SwiftkudiFormFeedback.showValidationErrors(form, data, {
                    boxId: 'service-contact-error-box',
                });
                return;
            }

            alert(data.message);
            if (data.success) {
                hideContactModal();
                document.getElementById('contact-form').reset();
                if (data.chat_url) {
                    window.location.href = data.chat_url;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.SwiftkudiFormFeedback) {
                window.SwiftkudiFormFeedback.showValidationErrors(form, {
                    message: 'An error occurred while sending your message. Please try again.',
                }, {
                    boxId: 'service-contact-error-box',
                });
            } else {
                alert('An error occurred. Please try again.');
            }
        });
    });
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideOrderModal();
            if (canContact) hideContactModal();
        }
    });

    // Close modal on backdrop click
    document.getElementById('order-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            hideOrderModal();
        }
    });
    
    document.getElementById('contact-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            hideContactModal();
        }
    });
</script>
@endpush
@endsection
