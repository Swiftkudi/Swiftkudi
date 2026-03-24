<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-indigo-700 dark:hover:bg-indigo-600 active:bg-indigo-900 dark:active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-dark-900 disabled:opacity-50 transition-all shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50']) }}>
    {{ $slot }}
</button>
