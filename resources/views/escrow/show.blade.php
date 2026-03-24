@extends('layouts.app')

@section('title', 'Escrow Details')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('escrow.index') }}" class="text-indigo-600 hover:text-indigo-500 text-sm">← Back to Escrow</a>
        </div>

        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Escrow #{{ $escrow->transaction_no }}</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500 dark:text-gray-400">Status:</span> <span class="text-gray-900 dark:text-gray-100 ml-2">{{ ucfirst($escrow->status) }}</span></div>
                <div><span class="text-gray-500 dark:text-gray-400">Amount:</span> <span class="text-gray-900 dark:text-gray-100 ml-2">₦{{ number_format((float)$escrow->amount, 2) }}</span></div>
                <div><span class="text-gray-500 dark:text-gray-400">Payer:</span> <span class="text-gray-900 dark:text-gray-100 ml-2">{{ $escrow->payer->name ?? 'N/A' }}</span></div>
                <div><span class="text-gray-500 dark:text-gray-400">Payee:</span> <span class="text-gray-900 dark:text-gray-100 ml-2">{{ $escrow->payee->name ?? 'N/A' }}</span></div>
                <div><span class="text-gray-500 dark:text-gray-400">Created:</span> <span class="text-gray-900 dark:text-gray-100 ml-2">{{ $escrow->created_at?->format('M d, Y H:i') }}</span></div>
                <div><span class="text-gray-500 dark:text-gray-400">Released:</span> <span class="text-gray-900 dark:text-gray-100 ml-2">{{ $escrow->released_at?->format('M d, Y H:i') ?? '—' }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
