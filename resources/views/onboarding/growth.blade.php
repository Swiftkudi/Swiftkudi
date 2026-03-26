@extends('layouts.app')

@section('title', 'Growth Marketplace Seller Onboarding')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold">Growth Marketplace Seller Onboarding</h1>
            <p class="text-gray-600">Create your first listing before marketplace browsing.</p>
        </div>

        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-6 mb-4">
            <p class="text-gray-500">Follow these steps now:</p>
            <ul class="list-disc list-inside mt-3 space-y-1">
                <li><strong>Step 1:</strong> Pay activation fee (₦{{ number_format(\App\Models\SystemSetting::get('growth_activation_fee', 1500), 2) }}).</li>
                <li><strong>Step 2:</strong> Create your first listing.</li>
                <li><strong>Step 3:</strong> Browse and manage your growth listings.</li>
            </ul>

            <div class="mt-4 space-y-2">
                @if(!auth()->user()->growth_activation_paid)
                    <form method="POST" action="{{ route('onboarding.growth.activate') }}">
                        @csrf
                        <button type="submit" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-black font-semibold px-5 py-2 rounded-lg">Pay Activation Fee</button>
                    </form>
                @else
                    <div class="text-green-700 bg-green-50 border border-green-200 p-3 rounded">Activation fee paid.</div>
                @endif

                <a href="{{ route('growth.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg">Create First Listing</a>
                <a href="{{ route('growth.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-lg">Browse Growth Marketplace</a>
            </div>

            <div class="mt-4 text-sm text-gray-600">
                <p>Activation paid: <strong>{{ auth()->user()->growth_activation_paid ? 'Yes' : 'No' }}</strong></p>
                <p>First listing created: <strong>{{ auth()->user()->growth_listing_created ? 'Yes' : 'No' }}</strong></p>
            </div>
        </div>

        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Back to dashboard</a>
    </div>
</div>
@endsection
