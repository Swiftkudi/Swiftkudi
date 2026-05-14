@extends('layouts.app')

@section('title', 'Post a New Listing')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-white mb-6">
        <i class="fas fa-plus-circle mr-2 text-blue-400"></i>Post a New Listing
    </h1>

    <form method="POST" action="{{ route('marketplace.listings.store') }}" enctype="multipart/form-data" class="bg-dark-800 rounded-2xl p-8 border border-dark-700">
        @csrf

        <!-- Title -->
        <div class="mb-6">
            <label class="form-label" for="title">Title *</label>
            <input type="text" name="title" id="title"
                   class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                   value="{{ old('title') }}" required>
            @error('title')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div class="mb-6">
            <label class="form-label" for="description">Description *</label>
            <textarea name="description" id="description" rows="5"
                      class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                      required>{{ old('description') }}</textarea>
            @error('description')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Category -->
            <div class="mb-4 md:mb-6">
                <label class="form-label" for="category_id">Category</label>
                <select name="category_id" id="category_id"
                        class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    <option value="">Select a category</option>
                    @foreach($categories as $category)
                        @if($category->parent_id === null)
                            <optgroup label="{{ $category->name }}">
                                @foreach($categories->where('parent_id', $category->id) as $sub)
                                    <option value="{{ $sub->id }}" {{ old('category_id') == $sub->id ? 'selected' : '' }}>
                                        {{ $sub->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
                @error('category_id')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Condition -->
            <div class="mb-4 md:mb-6">
                <label class="form-label" for="condition">Condition *</label>
                <select name="condition" id="condition"
                        class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                        required>
                    <option value="">Select condition</option>
                    <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>New</option>
                    <option value="like_new" {{ old('condition') == 'like_new' ? 'selected' : '' }}>Like New</option>
                    <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Good</option>
                    <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                    <option value="used" {{ old('condition') == 'used' ? 'selected' : '' }}>Used</option>
                </select>
                @error('condition')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Price -->
        <div class="mb-6">
            <label class="form-label" for="price">Price (₦) *</label>
            <input type="number" name="price" id="price" step="0.01" min="0"
                   class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                   value="{{ old('price') }}" required>
            @error('price')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Negotiable -->
            <div class="mb-4 md:mb-6 flex items-center">
                <input type="checkbox" name="negotiable" id="negotiable" value="1"
                       {{ old('negotiable') ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-dark-600 text-blue-500 focus:ring-blue-500">
                <label for="negotiable" class="ml-2 text-gray-300">Price is negotiable</label>
            </div>

            <!-- Available for Shipping -->
            <div class="mb-4 md:mb-6 flex items-center">
                <input type="checkbox" name="available_for_shipping" id="available_for_shipping" value="1"
                       {{ old('available_for_shipping') ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-dark-600 text-blue-500 focus:ring-blue-500">
                <label for="available_for_shipping" class="ml-2 text-gray-300">Available for shipping</label>
            </div>
        </div>

        <!-- Shipping Cost -->
        <div class="mb-6" id="shipping-cost-group" style="display: none;">
            <label class="form-label" for="shipping_cost">Shipping Cost (₦)</label>
            <input type="number" name="shipping_cost" id="shipping_cost" step="0.01" min="0"
                   class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                   value="{{ old('shipping_cost', 0) }}">
        </div>

        <!-- Location -->
        <div class="mb-6">
            <label class="form-label" for="location">Location</label>
            <input type="text" name="location" id="location"
                   class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                   value="{{ old('location') }}" placeholder="e.g., Room 301, Faculty of Science">
            @error('location')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tags -->
        <div class="mb-6">
            <label class="form-label" for="tags">Tags (comma-separated)</label>
            <input type="text" name="tags_text" id="tags_text"
                   class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                   value="{{ old('tags_text') }}" placeholder="e.g., textbooks, notes, electronics">
        </div>

        <!-- Images -->
        <div class="mb-6">
            <label class="form-label">Images</label>
            <div class="border-2 border-dashed border-dark-600 rounded-2xl p-8 text-center hover:border-blue-500 transition-colors"
                 id="image-dropzone">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-500 mb-2"></i>
                <p class="text-gray-400 text-sm">Click or drag images here</p>
                <p class="text-gray-500 text-xs mt-1">JPEG, PNG, JPG, WEBP up to 4MB each</p>
                <input type="file" name="images[]" multiple accept="image/*" id="image-input"
                       class="hidden" onchange="handleImagePreview(this)">
            </div>
            <div id="image-preview" class="grid grid-cols-4 gap-3 mt-4"></div>
            @error('images')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit -->
        <div class="flex gap-4">
            <button type="submit" name="publish" value="1" class="btn btn-primary flex-1">
                <i class="fas fa-paper-plane mr-2"></i>Publish & Submit for Review
            </button>
            <button type="submit" class="btn btn-secondary flex-1">
                <i class="fas fa-save mr-2"></i>Save as Draft
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle shipping cost field
    const shippingCheckbox = document.getElementById('available_for_shipping');
    const shippingGroup = document.getElementById('shipping-cost-group');
    if (shippingCheckbox) {
        shippingCheckbox.addEventListener('change', function() {
            shippingGroup.style.display = this.checked ? 'block' : 'none';
        });
        if (shippingCheckbox.checked) shippingGroup.style.display = 'block';
    }

    // Image preview
    window.handleImagePreview = function(input) {
        const preview = document.getElementById('image-preview');
        if (!input.files.length) return;
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-lg overflow-hidden bg-dark-700';
                div.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    };

    // Drag and drop
    const dropzone = document.getElementById('image-dropzone');
    if (dropzone) {
        dropzone.addEventListener('click', () => document.getElementById('image-input').click());
        ['dragenter', 'dragover'].forEach(e => {
            dropzone.addEventListener(e, ev => { ev.preventDefault(); dropzone.classList.add('border-blue-500'); });
        });
        ['dragleave', 'drop'].forEach(e => {
            dropzone.addEventListener(e, ev => { ev.preventDefault(); dropzone.classList.remove('border-blue-500'); });
        });
        dropzone.addEventListener('drop', ev => {
            const input = document.getElementById('image-input');
            input.files = ev.dataTransfer.files;
            handleImagePreview(input);
        });
    }
});
</script>
@endpush