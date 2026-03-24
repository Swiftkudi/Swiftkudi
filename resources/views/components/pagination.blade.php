@props(['paginator', 'showPerPage' => false, 'perPageOptions' => [10, 25, 50, 100]])

@if($paginator->hasPages())
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4">
    <!-- Results Info -->
    <div class="text-sm text-gray-600 dark:text-gray-400">
        Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </div>

    <div class="flex items-center gap-4">
        <!-- Per Page Selector -->
        @if($showPerPage)
        <div class="flex items-center gap-2">
            <label for="per-page" class="text-sm text-gray-600 dark:text-gray-400">Per page:</label>
            <select id="per-page" 
                    class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    onchange="window.location.href = updateQueryStringParameter(window.location.href, 'per_page', this.value)">
                @foreach($perPageOptions as $option)
                    <option value="{{ $option }}" {{ request('per_page', 15) == $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <!-- Pagination Links -->
        <nav class="flex items-center gap-1" role="navigation" aria-label="Pagination">
            <!-- Previous Page -->
            @if($paginator->onFirstPage())
                <button disabled
                        class="px-3 py-1.5 rounded-lg text-gray-400 dark:text-gray-600 cursor-not-allowed bg-gray-100 dark:bg-dark-800 text-sm"
                        aria-label="Previous page">
                    <i class="fas fa-chevron-left"></i>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="px-3 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-600 hover:bg-gray-50 dark:hover:bg-dark-800 text-sm transition-colors"
                   aria-label="Previous page">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            <!-- Page Numbers - Use simple iteration -->
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
            @endphp
            
            @if($startPage > 1)
                <a href="{{ $paginator->url(1) }}"
                   class="px-3 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-600 hover:bg-gray-50 dark:hover:bg-dark-800 text-sm transition-colors">
                    1
                </a>
                @if($startPage > 2)
                    <span class="px-3 py-1.5 text-gray-500 dark:text-gray-400">...</span>
                @endif
            @endif
            
            @for($page = $startPage; $page <= $endPage; $page++)
                @if($page == $currentPage)
                    <span class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm font-medium"
                          aria-current="page">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $paginator->url($page) }}"
                       class="px-3 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-600 hover:bg-gray-50 dark:hover:bg-dark-800 text-sm transition-colors"
                       aria-label="Go to page {{ $page }}">
                        {{ $page }}
                    </a>
                @endif
            @endfor
            
            @if($endPage < $lastPage)
                @if($endPage < $lastPage - 1)
                    <span class="px-3 py-1.5 text-gray-500 dark:text-gray-400">...</span>
                @endif
                <a href="{{ $paginator->url($lastPage) }}"
                   class="px-3 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-600 hover:bg-gray-50 dark:hover:bg-dark-800 text-sm transition-colors">
                    {{ $lastPage }}
                </a>
            @endif

            <!-- Next Page -->
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="px-3 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-600 hover:bg-gray-50 dark:hover:bg-dark-800 text-sm transition-colors"
                   aria-label="Next page">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <button disabled
                        class="px-3 py-1.5 rounded-lg text-gray-400 dark:text-gray-600 cursor-not-allowed bg-gray-100 dark:bg-dark-800 text-sm"
                        aria-label="Next page">
                    <i class="fas fa-chevron-right"></i>
                </button>
            @endif
        </nav>
    </div>
</div>

<script>
function updateQueryStringParameter(uri, key, value) {
    const url = new URL(uri, window.location.origin);
    url.searchParams.set(key, value);
    return url.toString();
}
</script>
@endif
