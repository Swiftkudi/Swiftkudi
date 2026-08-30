@extends('layouts.guest')

@section('title', 'Please try again shortly | SwiftKudi')

@section('content')
<div class="w-full max-w-lg mx-auto px-4 py-12">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-8 text-center shadow-sm">
        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-300">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Too many requests</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $message ?? 'Please try again shortly.' }}</p>
        <button type="button" onclick="history.back()" class="mt-6 inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Go back</button>
    </div>
</div>
@endsection
