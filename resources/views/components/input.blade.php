@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'rounded-lg border-gray-300 dark:border-dark-600 bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 transition-colors']) !!}>
