@extends('layouts.app')

@section('title', 'Freelancer Onboarding')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold">Freelancer Onboarding</h1>
            <p class="text-gray-600">Complete your profile and start bidding on projects.</p>
        </div>

        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-6 mb-4">
            <p class="text-gray-500">Complete these steps to unlock freelancer marketplace browsing and orders.</p>
            <ul class="list-disc list-inside mt-3 space-y-1">
                <li><strong>Step 1:</strong> Pay activation fee (₦{{ number_format(\App\Models\SystemSetting::get('freelancer_activation_fee', 1500), 2) }}).</li>
                <li><strong>Step 2:</strong> Complete your provider profile.</li>
                <li><strong>Step 3:</strong> Create your first service.</li>
            </ul>

            <div class="mt-4 space-y-2">
                @if(!auth()->user()->freelancer_activation_paid)
                    <form method="POST" action="{{ route('onboarding.freelancer.activate') }}">
                        @csrf
                        <button type="submit" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-black font-semibold px-5 py-2 rounded-lg">Pay Activation Fee</button>
                    </form>
                @else
                    <div class="text-green-700 bg-green-50 border border-green-200 p-3 rounded">Freelancer activation paid.</div>
                @endif

                <a href="{{ route('professional-services.edit-profile') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-lg">Edit Freelancer Profile</a>
                <a href="{{ route('professional-services.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg">Create First Service</a>
            </div>

            <div class="mt-4 text-sm text-gray-600">
                @if(auth()->user()->freelancer_profile_completed)
                    <p>Profile: <strong>Completed</strong></p>
                @else
                    <p>Profile: <strong>Pending</strong></p>
                @endif

                @if(auth()->user()->freelancer_service_created)
                    <p>First service: <strong>Created</strong></p>
                @else
                    <p>First service: <strong>Pending</strong></p>
                @endif
            </div>
        </div>

        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Back to dashboard</a>
    </div>
</div>
@endsection
