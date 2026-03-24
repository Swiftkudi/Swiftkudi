@extends('layouts.app')

@section('title', $profile->user->name . ' - Service Provider - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm mb-6">
            <a href="{{ route('professional-services.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i class="fas fa-briefcase mr-1"></i> Services
            </a>
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $profile->user->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Profile Header -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white">
                        <div class="flex items-center gap-6">
                            <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center text-3xl font-bold">
                                {{ substr($profile->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold">{{ $profile->user->name }}</h1>
                                <p class="opacity-90">Service Provider</p>
                                @if($profile->is_available)
                                    <span class="inline-flex items-center gap-1 mt-2 px-3 py-1 bg-green-400/20 rounded-full text-sm">
                                        <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                                        Available for hire
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($profile->bio)
                    <div class="p-6">
                        <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-3">About</h2>
                        <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $profile->bio }}</p>
                    </div>
                    @endif

                    @if($profile->skills && count($profile->skills) > 0)
                    <div class="p-6 border-t border-gray-100 dark:border-dark-700">
                        <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-3">Skills</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($profile->skills as $skill)
                                <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 rounded-full text-sm">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Services by this provider -->
                <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Services by {{ $profile->user->name }}</h2>
                    
                    @if($services->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($services as $service)
                                <a href="{{ route('professional-services.show', $service->id) }}" 
                                   class="block p-4 bg-gray-50 dark:bg-dark-800 rounded-xl hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $service->title }}</h3>
                                    @if($service->category)
                                        <span class="text-xs text-indigo-600 dark:text-indigo-400">{{ $service->category->name }}</span>
                                    @endif
                                    <div class="flex items-center justify-between mt-3">
                                        <span class="text-lg font-bold text-green-600 dark:text-green-400">₦{{ number_format($service->price) }}</span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $service->delivery_days }} days</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No active services yet</p>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    <!-- Contact Card -->
                    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-4">Contact {{ $profile->user->name }}</h3>
                        
                        @auth
                            <button onclick="showContactModal()" 
                                    class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                                <i class="fas fa-comment-dots mr-2"></i>
                                Send Message
                            </button>
                        @else
                            <a href="{{ route('login') }}" 
                               class="block w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30 text-center">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Login to Contact
                            </a>
                        @endauth

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-100 dark:border-dark-700">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $services->count() }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Services</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $profile->rating ?? '0.0' }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Rating</div>
                            </div>
                        </div>
                    </div>

                    <!-- Response Time -->
                    @if($profile->response_time)
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-500/10 dark:to-emerald-500/10 rounded-2xl p-6 border border-green-200 dark:border-green-500/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-green-800 dark:text-green-300">Response Time</h4>
                                <p class="text-sm text-green-700 dark:text-green-400">{{ $profile->response_time }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Modal -->
@auth
<div id="contact-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-dark-900 rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 rounded-t-3xl">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">Send Message</h2>
                <button onclick="hideContactModal()" class="text-white/80 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <p class="text-indigo-200 mt-1">To: {{ $profile->user->name }}</p>
        </div>

        <!-- Modal Body -->
        <form id="contact-form" class="p-6 space-y-6">
            <input type="hidden" name="recipient_id" value="{{ $profile->user->id }}">
            
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
                        class="flex-1 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-paper-plane mr-2"></i>Send
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<div
    id="provider-profile-config"
    class="hidden"
    data-contact-url="{{ route('professional-services.contact') }}"
    data-can-contact="{{ auth()->check() ? '1' : '0' }}"
></div>

@push('scripts')
<script>
    const providerProfileConfig = document.getElementById('provider-profile-config');
    const contactUrl = providerProfileConfig?.dataset.contactUrl || '';
    const canContact = providerProfileConfig?.dataset.canContact === '1';

    function showContactModal() {
        if (!canContact) {
            return;
        }

        document.getElementById('contact-modal')?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideContactModal() {
        document.getElementById('contact-modal')?.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Handle form submission
    document.getElementById('contact-form')?.addEventListener('submit', function(e) {
        if (!canContact || !contactUrl) {
            return;
        }

        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        
        fetch(contactUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                hideContactModal();
                document.getElementById('contact-form').reset();
            } else {
                if ((data.errors || data.error_list) && window.SwiftkudiFormFeedback) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, data, {
                        boxId: 'provider-contact-error-box',
                    });
                } else {
                    alert(data.message || 'Failed to send message');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.SwiftkudiFormFeedback) {
                window.SwiftkudiFormFeedback.showValidationErrors(form, {
                    message: 'An error occurred while sending your message. Please try again.',
                }, {
                    boxId: 'provider-contact-error-box',
                });
            } else {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideContactModal();
        }
    });

    // Close modal on backdrop click
    document.getElementById('contact-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            hideContactModal();
        }
    });
</script>
@endpush
@endsection
