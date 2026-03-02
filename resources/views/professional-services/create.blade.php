@extends('layouts.app')

@section('title', 'Create Service - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Create New Service</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Offer your professional services to other users</p>
        </div>

        <form id="serviceForm" class="space-y-6">
            @csrf
            
            <!-- Basic Info -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Basic Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Service Title</label>
                        <input type="text" name="title" required minlength="5" maxlength="255"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., Professional Website Development">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                        <select name="category_id" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" required minlength="20" maxlength="5000" rows="6"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Describe your service in detail..."></textarea>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Minimum 20 characters</p>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Pricing & Delivery</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price (₦)</label>
                        <input type="number" name="price" required min="100" step="0.01"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="5000">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Delivery Days</label>
                        <input type="number" name="delivery_days" required min="1" max="30"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="7">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Revisions Included</label>
                        <input type="number" name="revisions_included" required min="0" max="5" value="1"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Add-ons -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Extra Add-ons</h2>
                    <button type="button" id="addAddon" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                        + Add Extra
                    </button>
                </div>
                
                <div id="addonsContainer" class="space-y-3">
                    <!-- Add-ons will be added here -->
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('professional-services.my-services') }}" 
                    class="px-6 py-2.5 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-800 transition-all">
                    Cancel
                </a>
                <button type="submit" 
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium hover:opacity-90 transition-all shadow-lg shadow-indigo-500/30">
                    Create Service
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('serviceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating...';

    try {
        const response = await fetch('{{ route("professional-services.store") }}', {
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
                        boxId: 'service-create-error-box',
                    });
                } else {
                    alert(data.message || 'We could not create your service. Please review your form and try again.');
                }
            }
        } else {
            // Response is HTML (可能是错误页面或重定向)
            const text = await response.text();
            if (response.ok) {
                // 可能成功重定向
                window.location.href = '{{ route("professional-services.my-services") }}';
            } else {
                console.error('Error response:', text);
                if (window.SwiftkudiFormFeedback) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, {
                        message: 'We could not process your request. Please check the form and try again.',
                    }, {
                        boxId: 'service-create-error-box',
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
                boxId: 'service-create-error-box',
            });
        } else {
            alert('Error: ' + error.message);
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Service';
    }
});

let addonCount = 0;
document.getElementById('addAddon').addEventListener('click', function() {
    addonCount++;
    const container = document.getElementById('addonsContainer');
    const addonHtml = `
        <div class="flex gap-3 items-start p-4 bg-gray-50 dark:bg-dark-800 rounded-xl">
            <div class="flex-1">
                <input type="text" name="addons[${addonCount}][name]" placeholder="Add-on name"
                    class="w-full px-3 py-2 bg-white dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-gray-100">
            </div>
            <div class="w-24">
                <input type="number" name="addons[${addonCount}][price]" placeholder="Price" min="0" step="0.01"
                    class="w-full px-3 py-2 bg-white dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-gray-100">
            </div>
            <div class="w-24">
                <input type="number" name="addons[${addonCount}][delivery_days_extra]" placeholder="Days" min="0"
                    class="w-full px-3 py-2 bg-white dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-gray-100">
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 p-2">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', addonHtml);
});
</script>
@endsection
