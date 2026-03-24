@extends('admin.layout')

@section('title', 'Create Category - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.marketplace.index') }}" class="text-indigo-600 hover:text-indigo-500 flex items-center gap-2 mb-4">
                <i class="fas fa-arrow-left"></i> Back to Marketplace
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Category</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Add a new category to your marketplace</p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-8">
            <form method="POST" action="{{ route('admin.marketplace.store') }}">
                @csrf

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Category Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                            placeholder="e.g., Social Media Marketing">
                        @error('name')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Category Type *</label>
                        <select name="type" required class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Parent Category -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Parent Category</label>
                        <select name="parent_id" class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            <option value="">No Parent (Top Level)</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Icon & Color -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Icon (Font Awesome)</label>
                            <input type="text" name="icon" value="{{ old('icon', 'fas fa-folder') }}"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                placeholder="fas fa-folder">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Color</label>
                            <input type="text" name="color" value="{{ old('color', '#6366f1') }}"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                placeholder="#6366f1">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                            placeholder="Brief description of this category">{{ old('description') }}</textarea>
                    </div>

                    <!-- Order -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Display Order</label>
                        <input type="number" name="order" value="{{ old('order', 0) }}" min="0"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                            placeholder="0">
                    </div>

                    <!-- Status -->
                    <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                class="rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Category is active</span>
                        </label>
                    </div>

                    <!-- Meta Fields -->
                    <div class="border-t border-gray-200 dark:border-dark-700 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">SEO Settings</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Meta Title</label>
                                <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                                    class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                    placeholder="SEO title for this category">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Meta Description</label>
                                <textarea name="meta_description" rows="2"
                                    class="w-full px-4 py-3 border border-gray-200 dark:border-dark-700 rounded-xl bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                    placeholder="SEO description">{{ old('meta_description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end gap-4 pt-6">
                        <a href="{{ route('admin.marketplace.index') }}" class="px-6 py-3 border border-gray-200 dark:border-dark-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all font-medium">
                            <i class="fas fa-plus mr-2"></i> Create Category
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
