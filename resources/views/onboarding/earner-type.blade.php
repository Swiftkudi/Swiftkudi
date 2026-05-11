@extends('layouts.app')

@section('title', 'Choose Earner Type')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">Choose Earner Type</h1>
            <p class="text-gray-600">Choose how you want to earn on SwiftKudi.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-5 shadow-md">
                <h3 class="text-xl font-bold mb-2">UGC Earner</h3>
                <p class="text-gray-500 mb-4">Start earning with micro tasks and user-generated content.</p>
                <form method="POST" action="{{ route('onboarding.select.post') }}">
                    @csrf
                    <input type="hidden" name="account_type" value="earner" />
                    <button type="submit" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">Continue as UGC Earner</button>
                </form>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-5 shadow-md">
                <h3 class="text-xl font-bold mb-2">Freelancing</h3>
                <p class="text-gray-500 mb-4">Activate freelancer onboarding for professional services.</p>
                <form method="POST" action="{{ route('onboarding.select.post') }}">
                    @csrf
                    <input type="hidden" name="account_type" value="freelancer" />
                    <button type="submit" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">Continue as Freelancer</button>
                </form>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('onboarding.select') }}" class="text-gray-600 hover:text-indigo-600">Back to main onboarding selection</a>
        </div>
    </div>
</div>
@endsection