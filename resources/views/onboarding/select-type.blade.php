@extends('layouts.app')

@section('title', 'Select Your Onboarding Path')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">Welcome to SwiftKudi Onboarding</h1>
            <p class="text-gray-600">Choose what you want to do first to personalize your experience.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php $options = [
                ['label'=>'Earn Money', 'value'=>'earner', 'description'=>'Activate and complete a starter task to start earning quickly.'],
                ['label'=>'Create Tasks', 'value'=>'task_creator', 'description'=>'Post campaigns and hire workers to fulfill orders.'],
                ['label'=>'Freelancing', 'value'=>'freelancer', 'description'=>'Offer professional services and get hired.'],
                ['label'=>'Sell Digital Products', 'value'=>'digital_seller', 'description'=>'Upload your software/plugins/digital products and sell instantly.'],
                ['label'=>'Growth Marketplace', 'value'=>'growth_seller', 'description'=>'Create growth listings and get clients for backlinks, leads, etc.'],
                ['label'=>'Buy Services', 'value'=>'buyer', 'description'=>'Shop for verified services and products.'],
            ]; @endphp

            @foreach($options as $option)
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-5 shadow-md">
                <h3 class="text-xl font-bold mb-2">{{ $option['label'] }}</h3>
                <p class="text-gray-500 mb-4">{{ $option['description'] }}</p>
                <form method="POST" action="{{ route('onboarding.select.post') }}">
                    @csrf
                    <input type="hidden" name="account_type" value="{{ $option['value'] }}" />
                    <button type="submit" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">Choose</button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
