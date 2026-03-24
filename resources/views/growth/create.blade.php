@extends('layouts.app')

@section('title', 'Create Growth Listing - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('growth.index') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2 mb-4">
                <i class="fas fa-arrow-left"></i> Back to Growth Marketplace
            </a>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Create Growth Listing</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Offer backlinks, influencer promotion, newsletters, or leads</p>
        </div>

        <!-- Type Selection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Listing Type</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($types as $key => $label)
                    <a href="{{ route('growth.create', ['type' => $key]) }}" 
                        class="p-4 border rounded-xl text-center transition-all hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 
                        {{ $type === $key ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/20' : 'border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800' }}">
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $label }}</div>
                    </a>
                @endforeach
            </div>
        </div>

        <form id="listingForm" class="space-y-6">
            @csrf
            
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Listing Details</h2>
                
                <input type="hidden" name="type" value="{{ $type }}">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                        <input type="text" name="title" required minlength="5" maxlength="255"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., DA50+ Backlinks from Tech Blogs">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" required minlength="20" maxlength="5000" rows="6"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Describe what you're offering..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price (₦)</label>
                            <input type="number" name="price" required min="100" step="0.01"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Delivery Days</label>
                            <input type="number" name="delivery_days" required min="1" max="30"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Type-specific fields -->
            @if(!empty($specsFields))
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ $types[$type] ?? 'Listing' }} Details</h2>
                <div class="space-y-4">
                    @foreach($specsFields as $spec)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $spec['label'] }}
                                @if(!empty($spec['required']))<span class="text-red-500">*</span>@endif
                            </label>
                            @if(($spec['type'] ?? 'text') === 'select' && !empty($spec['options']))
                                <select name="specs[{{ $spec['name'] }}]" 
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    {{ !empty($spec['required']) ? 'required' : '' }}>
                                    <option value="">Select...</option>
                                    @foreach($spec['options'] as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $spec['type'] ?? 'text' }}" name="specs[{{ $spec['name'] }}]"
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    {{ !empty($spec['required']) ? 'required' : '' }}>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="flex justify-end gap-3">
                <a href="{{ route('growth.my-listings') }}" 
                    class="px-6 py-2.5 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800 transition-all">
                    Cancel
                </a>
                <button type="submit" 
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium hover:opacity-90 transition-all shadow-lg shadow-indigo-500/30">
                    Create Listing
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('listingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating...';

    try {
        const response = await fetch('{{ route("growth.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            const data = await response.json();
            
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                if ((response.status === 422 || data.errors || data.error_list) && window.SwiftkudiFormFeedback) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, data, {
                        boxId: 'growth-create-error-box',
                    });
                } else {
                    alert(data.message || 'We could not create your listing. Please review your form and try again.');
                }
            }
        } else {
            // Response is HTML (，可能是错误页面或重定向)
            const text = await response.text();
            if (response.ok) {
                // 可能成功重定向
                window.location.href = '{{ route("growth.my-listings") }}';
            } else {
                console.error('Error response:', text);
                if (window.SwiftkudiFormFeedback) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, {
                        message: 'We could not process your request. Please check the form and try again.',
                    }, {
                        boxId: 'growth-create-error-box',
                    });
                } else {
                    alert('Error: Server returned ' + response.status);
                }
            }
        }
    } catch (error) {
        console.error('Error:', error);
        if (window.SwiftkudiFormFeedback) {
            window.SwiftkudiFormFeedback.showValidationErrors(form, {
                message: 'Network error. Please check your internet connection and try again.',
            }, {
                boxId: 'growth-create-error-box',
            });
        } else {
            alert('Error: ' + error.message);
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Listing';
    }
});
</script>
@endsection
