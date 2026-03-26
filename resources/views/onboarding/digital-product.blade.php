@extends('layouts.app')

@section('title', 'Digital Product Seller Onboarding')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold">Digital Product Seller Onboarding</h1>
            <p class="text-gray-600">Upload your first digital product before marketplace browsing.</p>
        </div>

        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-6 mb-4">
            <p class="text-gray-500">Follow these steps now:</p>
            <ul class="list-disc list-inside mt-3 space-y-1">
                <li><strong>Step 1:</strong> Activate Account .</li>
                <li><strong>Step 2:</strong> Upload your first product.</li>
                <li><strong>Step 3:</strong> Browse and manage your digital products.</li>
            </ul>

            <div class="mt-4 space-y-2">
                @if(!$isActivated)
                    <a href="{{ route('dashboard') }}" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-black font-semibold px-5 py-2 rounded-lg">Start Activation</a>
                @else
                    <div class="text-white bg-green-500 border border-green-200 p-3 rounded">Activation Done.</div>
                @endif

                <a href="{{ route('digital-products.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg">Upload First Product</a>
                <a href="{{ route('digital-products.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-lg">Browse Digital Products</a>
            </div>

            {{-- Cache user data to avoid multiple DB queries --}}
            @php
                $user = auth()->user();
                $isActivated = $isActivated;
                $hasUploadedProduct = $user && $user->digital_product_uploaded;
            @endphp

            <div class="mt-4 text-sm text-gray-600">
                <p>
                    <span class="font-medium">Activation Done:</span>
                    <strong>{{ $isActivated ? 'Yes' : 'No' }}</strong>
                    @if(!$isActivated)
                        <span class="text-amber-600 ml-1">(Required)</span>
                    @endif
                </p>
                <p>
                    <span class="font-medium">First product uploaded:</span>
                    <strong>{{ $hasUploadedProduct ? 'Yes' : 'No' }}</strong>
                    @if(!$hasUploadedProduct)
                        <span class="text-amber-600 ml-1">(Required)</span>
                    @endif
                </p>
            </div>
        </div>

        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Back to dashboard</a>
    </div>
</div>
@endsection
