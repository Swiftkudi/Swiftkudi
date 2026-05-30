@extends('layouts.app')

@section('title', 'Support')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-dark-800 rounded-3xl border border-dark-700 p-8 shadow-sm">
        <h1 class="text-3xl font-bold text-white mb-4">Need help?</h1>
        <p class="text-gray-300 mb-6">Our support team is here to help you with marketplace questions, seller issues, payment concerns, and account support.</p>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-3xl bg-dark-700 p-6 border border-dark-600">
                <h2 class="text-xl font-semibold text-white mb-3">Contact Support</h2>
                <p class="text-gray-400 mb-4">Send us an email and we will respond as quickly as possible.</p>
                <a href="mailto:support@swiftkudi.com" class="inline-flex items-center justify-center rounded-2xl bg-blue-500 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-400 transition">Email Support</a>
            </div>
            <div class="rounded-3xl bg-dark-700 p-6 border border-dark-600">
                <h2 class="text-xl font-semibold text-white mb-3">Marketplace help</h2>
                <p class="text-gray-400 mb-4">Use the marketplace chat and order support tools if you need help with a specific listing or buyer/seller interaction.</p>
                <a href="{{ route('marketplace.listings.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-blue-500 bg-transparent px-5 py-3 text-sm font-semibold text-blue-400 hover:bg-blue-500/10 transition">Return to Marketplace</a>
            </div>
        </div>

        <div class="mt-8 rounded-3xl bg-dark-700 p-6 border border-dark-600">
            <h2 class="text-xl font-semibold text-white mb-3">Quick support tips</h2>
            <ul class="space-y-3 text-gray-400 list-disc list-inside">
                <li>Include your account email and marketplace item details.</li>
                <li>Attach screenshots when reporting an issue with a listing.</li>
                <li>If your question is order-related, please include the order number.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
