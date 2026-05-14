<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\OrderService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerDashboardController extends Controller
{
    protected OrderService $orderService;

    public function __construct()
    {
        $this->orderService = app(OrderService::class);
    }

    public function index()
    {
        $seller = Auth::user();
        $today = now()->startOfDay();

        $stats = [
            'total_listings' => $seller->marketplaceListings()->count(),
            'active_listings' => $seller->marketplaceListings()->active()->count(),
            'total_sales' => $seller->marketplaceOrdersAsSeller()->where('status', 'completed')->count(),
            'pending_orders' => $seller->marketplaceOrdersAsSeller()->whereIn('status', ['pending', 'paid', 'in_progress'])->count(),
            'total_revenue' => $seller->marketplaceOrdersAsSeller()->where('status', 'completed')->sum('amount') ?? 0,
            'rating' => $seller->seller_rating ?? 0,
            'rating_count' => $seller->seller_rating_count ?? 0,
            'today_orders' => $seller->marketplaceOrdersAsSeller()->where('created_at', '>=', $today)->count(),
        ];

        $recentOrders = $seller->marketplaceOrdersAsSeller()
            ->with(['listing', 'buyer'])
            ->latest()
            ->limit(10)
            ->get();

        return view('marketplace.seller.dashboard', compact('stats', 'recentOrders'));
    }

    public function listings()
    {
        $listings = Auth::user()->marketplaceListings()
            ->withCount('orders')
            ->latest()
            ->paginate(20);

        return view('marketplace.seller.listings', compact('listings'));
    }

    public function orders()
    {
        $orders = Auth::user()->marketplaceOrdersAsSeller()
            ->with(['listing', 'buyer', 'escrow'])
            ->latest()
            ->paginate(20);

        return view('marketplace.seller.orders', compact('orders'));
    }

    public function reviews()
    {
        $reviews = \App\Models\Marketplace\Review::query()
            ->with(['order', 'reviewer'])
            ->where('reviewed_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('marketplace.seller.reviews', compact('reviews'));
    }

    public function subscription()
    {
        $user = Auth::user();
        $isPremium = app(\App\Services\Marketplace\SubscriptionService::class)->isPremium($user);
        $activeSub = \App\Models\UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('metadata->marketplace_plan', true)
            ->first();

        return view('marketplace.subscription.index', compact('isPremium', 'activeSub'));
    }

    public function profile()
    {
        $seller = Auth::user();

        return view('marketplace.seller.profile', compact('seller'));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'marketplace_bio' => 'nullable|string|max:2000',
            'marketplace_contact_preferences' => 'nullable|array',
            'marketplace_avatar' => 'nullable|image|max:4096',
        ]);

        $user = Auth::user();

        if ($request->hasFile('marketplace_avatar')) {
            $validated['marketplace_avatar'] = $request->file('marketplace_avatar')->store('marketplace/avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Seller profile updated successfully!');
    }
}