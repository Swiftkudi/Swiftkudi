@extends('layouts.app')

@section('title', 'Edit Listing')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-white mb-6">
        <i class="fas fa-edit mr-2 text-blue-400"></i>Edit Listing
    </h1>

    <form method="POST" action="{{ route('marketplace.listings.update', $listing->id) }}" enctype="multipart/form-data" class="bg-dark-800 rounded-2xl p-8 border border-dark-700">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="mb-4 md:mb-6">
                <label class="form-label" for="title">Title *</label>
                <input type="text" name="title" id="title"
                       class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                       value="{{ old('title', $listing->title) }}" required>
                @error('title')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4 md:mb-6">
                <label class="form-label" for="condition">Condition *</label>
                <select name="condition" id="condition"
                        class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                        required>
                    <option value="new" {{ $listing->condition == 'new' ? 'selected' : '' }}>New</option>
                    <option value="like_new" {{ $listing->condition == 'like_new' ? 'selected' : '' }}>Like New</option>
                    <option value="good" {{ $listing->condition == 'good' ? 'selected' : '' }}>Good</option>
                    <option value="fair" {{ $listing->condition == 'fair' ? 'selected' : '' }}>Fair</option>
                    <option value="used" {{ $listing->condition == 'used' ? 'selected' : '' }}>Used</option>
                </select>
            </div>
        </div>

        <div class="mb-6">
            <label class="form-label" for="description">Description *</label>
            <textarea name="description" id="description" rows="5"
                      class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                      required>{{ old('description', $listing->description) }}</textarea>
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
                                    <option value="{{ $sub->id }}" {{ old('category_id', $listing->category_id) == $sub->id ? 'selected' : '' }}>
                                        {{ $sub->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Price -->
            <div class="mb-4 md:mb-6">
                <label class="form-label" for="price">Price (₦) *</label>
                <input type="number" name="price" id="price" step="0.01" min="0"
                       class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                       value="{{ old('price', $listing->price) }}" required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="mb-4 md:mb-6 flex items-center">
                <input type="checkbox" name="negotiable" id="negotiable" value="1"
                       {{ $listing->negotiable ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-dark-600 text-blue-500 focus:ring-blue-500">
                <label for="negotiable" class="ml-2 text-gray-300">Price is negotiable</label>
            </div>

            <div class="mb-4 md:mb-6 flex items-center">
                <input type="checkbox" name="available_for_shipping" id="available_for_shipping" value="1"
                       {{ $listing->available_for_shipping ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-dark-600 text-blue-500 focus:ring-blue-500">
                <label for="available_for_shipping" class="ml-2 text-gray-300">Available for shipping</label>
            </div>
        </div>

        <div class="mb-6" id="shipping-cost-group" style="display: {{ $listing->available_for_shipping ? 'block' : 'none' }}">
            <label class="form-label" for="shipping_cost">Shipping Cost (₦)</label>
            <input type="number" name="shipping_cost" id="shipping_cost" step="0.01" min="0"
                   class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                   value="{{ old('shipping_cost', $listing->shipping_cost) }}">
        </div>

        <div class="mb-6">
            <label class="form-label" for="location">Location</label>
            <input type="text" name="location" id="location"
                   class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                   value="{{ old('location', $listing->location) }}" placeholder="e.g., Room 301, Faculty of Science">
        </div>

        <div class="mb-6">
            <label class="form-label" for="tags">Tags</label>
            <input type="text" name="tags_text"
                   class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                   value="{{ old('tags_text', implode(', ', $listing->tags ?? [])) }}"
                   placeholder="e.g., textbooks, notes, electronics">
        </div>

        <div class="flex gap-4">
            <button type="submit" name="publish" value="1" class="btn btn-primary flex-1">
                <i class="fas fa-paper-plane mr-2"></i>Update & Submit for Review
            </button>
            <button type="submit" class="btn btn-secondary flex-1">
                <i class="fas fa-save mr-2"></i>Save as Draft
            </button>
            <a href="{{ route('marketplace.listings.show', $listing->slug) }}" class="btn btn-secondary flex-1 text-center">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const shippingCheckbox = document.getElementById('available_for_shipping');
    const shippingGroup = document.getElementById('shipping-cost-group');
    if (shippingCheckbox) {
        shippingCheckbox.addEventListener('change', function() {
            shippingGroup.style.display = this.checked ? 'block' : 'none';
        });
    }
});
</script>
@endpush