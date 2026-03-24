@extends('layouts.app')

@section('title', 'Edit: ' . $product->title . ' - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('digital-products.show', $product) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 flex items-center gap-2 transition-colors">
                <i class="fas fa-arrow-left"></i> Back to Product
            </a>
        </div>

        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Product</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Update your product details</p>
            </div>

            <form method="POST" action="{{ route('digital-products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Product Title *</label>
                    <input type="text" name="title" value="{{ old('title', $product->title) }}" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Description *</label>
                    <textarea name="description" rows="6" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">{{ old('description', $product->description) }}</textarea>
                </div>

                <!-- Category & Tags -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Category</label>
                        <select name="category_id" class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tags</label>
                        <input type="text" name="tags" value="{{ old('tags', implode(', ', $product->tag_list)) }}"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Pricing -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Price (₦) *</label>
                        <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" step="0.01" required
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Sale Price (₦)</label>
                        <input type="number" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" min="0" step="0.01"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }}
                            class="rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Product is active and visible</span>
                    </label>
                </div>

                <!-- License Type -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">License Type *</label>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="flex flex-col items-center p-4 border-2 border-gray-200 dark:border-dark-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-800 transition-all {{ $product->license_type == 1 ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                            <input type="radio" name="license_type" value="1" {{ $product->license_type == 1 ? 'checked' : '' }} class="sr-only">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Personal</span>
                        </label>
                        <label class="flex flex-col items-center p-4 border-2 border-gray-200 dark:border-dark-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-800 transition-all {{ $product->license_type == 2 ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                            <input type="radio" name="license_type" value="2" {{ $product->license_type == 2 ? 'checked' : '' }} class="sr-only">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Commercial</span>
                        </label>
                        <label class="flex flex-col items-center p-4 border-2 border-gray-200 dark:border-dark-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-800 transition-all {{ $product->license_type == 3 ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                            <input type="radio" name="license_type" value="3" {{ $product->license_type == 3 ? 'checked' : '' }} class="sr-only">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Extended</span>
                        </label>
                    </div>
                </div>

                <!-- Version & Requirements -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Version</label>
                        <input type="number" name="version" value="{{ old('version', $product->version) }}" min="1"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Requirements</label>
                        <input type="text" name="requirements" value="{{ old('requirements', $product->requirements) }}"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Changelog -->
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Changelog</label>
                    <textarea name="changelog" rows="4"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">{{ old('changelog', $product->changelog) }}</textarea>
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('digital-products.show', $product) }}" class="px-6 py-3 border border-gray-200 dark:border-dark-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all font-medium">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
