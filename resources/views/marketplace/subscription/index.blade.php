@extends('layouts.app')

@section('title', 'Subscription - Marketplace')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-white mb-6">
        <i class="fas fa-crown mr-2 text-yellow-400"></i>Marketplace Subscription
    </h1>

    @if($isPremium)
    <div class="bg-dark-800 rounded-2xl p-8 border border-green-500/30 text-center">
        <i class="fas fa-crown text-6xl text-yellow-400 mb-4"></i>
        <h2 class="text-2xl font-bold text-green-400 mb-2">Premium Seller</h2>
        <p class="text-gray-400 mb-4">You are an active premium seller. Enjoy lower commission rates and priority support!</p>

        @if($activeSub)
        <div class="bg-dark-700 rounded-xl p-4 inline-block">
            <p class="text-gray-300 text-sm">
                Expires: <span class="text-white font-medium">{{ $activeSub->expires_at->format('M d, Y') }}</span>
            </p>
        </div>
        <form method="POST" action="{{ route('marketplace.subscription.cancel') }}" class="mt-4 inline-block">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm"
                    onclick="return confirm('Cancel your premium subscription? You can re-subscribe anytime.')">
                <i class="fas fa-times mr-2"></i>Cancel Subscription
            </button>
        </form>
        @endif
    </div>
    @else
    <div class="bg-dark-800 rounded-2xl overflow-hidden border border-dark-700 mb-8">
        <div class="p-8 text-center">
            <div class="w-20 h-20 rounded-full bg-yellow-500/10 border-2 border-yellow-500 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-crown text-4xl text-yellow-400"></i>
            </div>
            <h2 class="text-xl font-bold text-white mb-2">Upgrade to Premium Seller</h2>
            <p class="text-gray-400 mb-6">Get lower commission rates, priority support, and increased visibility on the marketplace.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-lg mx-auto mb-8">
                <div class="bg-dark-700 rounded-xl p-4">
                    <p class="text-2xl font-bold text-white">₦{{ number_format($price, 2) }}</p>
                    <p class="text-gray-500 text-sm">per month</p>
                </div>
                <div class="bg-dark-700 rounded-xl p-4">
                    <p class="text-white font-semibold text-sm mb-2">Benefits:</p>
                    <ul class="text-left text-gray-400 text-xs space-y-1">
                        <li><i class="fas fa-check text-green-400 mr-2"></i>2% commission rate</li>
                        <li><i class="fas fa-check text-green-400 mr-2"></i>Priority support</li>
                        <li><i class="fas fa-check text-green-400 mr-2"></i>Featured listing boost</li>
                        <li><i class="fas fa-check text-green-400 mr-2"></i>Advanced analytics</li>
                    </ul>
                </div>
                <div class="bg-dark-700 rounded-xl p-4 flex items-end">
                    <form method="POST" action="{{ route('marketplace.subscription.subscribe') }}" class="w-full">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-bolt mr-2"></i>Subscribe Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Payment History -->
    <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700">
        <h3 class="text-white font-semibold mb-4">Recent Transactions</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="text-left text-gray-400 border-b border-dark-700">
                    <th class="py-3 px-4">Date</th>
                    <th class="py-3 px-4">Type</th>
                    <th class="py-3 px-4">Amount</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach(\App\Models\UserSubscription::where('user_id', Auth::id())->latest()->limit(5)->get() as $sub)
                <tr class="border-b border-dark-700/50">
                    <td class="py-3 px-4 text-gray-300">{{ $sub->created_at->format('M d, Y') }}</td>
                    <td class="py-3 px-4 text-gray-300">Marketplace Subscription</td>
                    <td class="py-3 px-4 font-medium text-white">₦{{ number_format($sub->amount_paid, 2) }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $sub->status == 'active' ? 'bg-green-500/10 text-green-400' : ($sub->status == 'cancelled' ? 'bg-red-500/10 text-red-400' : 'bg-gray-500/10 text-gray-400') }}">
                            {{ ucfirst($sub->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection