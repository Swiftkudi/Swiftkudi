<div class="rounded-3xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 p-5">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @forelse($categories as $category)
            <label class="category-card cursor-pointer rounded-3xl border border-gray-200 dark:border-dark-700 p-4 flex items-center gap-4 transition-colors">
                <div class="check-box flex items-center justify-center h-12 w-12 rounded-2xl border border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 transition-colors">
                    <svg class="check-icon h-6 w-6 text-white hidden" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.704 5.296a1 1 0 00-1.408 0L8 12.592 4.704 9.296a1 1 0 00-1.408 1.408l4 4a1 1 0 001.408 0l8-8a1 1 0 000-1.408z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $category->name }}</div>
                    @if(!empty($category->description))
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $category->description }}</div>
                    @endif
                </div>
                <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="hidden" {{ in_array($category->id, old('categories', $selectedCategories ?? [])) ? 'checked' : '' }} />
            </label>
        @empty
            <div class="text-sm text-gray-500 dark:text-gray-400">No categories available at the moment.</div>
        @endforelse
    </div>
</div>
