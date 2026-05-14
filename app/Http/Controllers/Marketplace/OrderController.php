<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\OrderService;
use App\Models\Marketplace\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct()
    {
        $this->orderService = app(OrderService::class);
    }

    public function index(Request $request)
    {
        $orders = \App\Models\Marketplace\Order::query()
            ->with(['listing', 'seller', 'escrow'])
            ->where('buyer_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('marketplace.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = \App\Models\Marketplace\Order::with(['listing', 'seller', 'buyer', 'escrow', 'reviews'])
            ->findOrFail($id);

        if ($order->buyer_id !== Auth::id() && $order->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('marketplace.orders.show', compact('order'));
    }

    public function create(Listing $listing)
    {
        if ($listing->status !== Listing::STATUS_ACTIVE) {
            abort(404);
        }

        if (Auth::id() === $listing->user_id) {
            abort(403, 'Cannot purchase your own listing');
        }

        $seller = $listing->seller;
        $canPurchase = Auth::check() && Auth::user()->wallet !== null;

        if (!$canPurchase) {
            return redirect()->route('marketplace.listings.show', $listing->slug)
                ->with('error', 'You need a wallet to make a purchase.');
        }

        return view('marketplace.orders.create', compact('listing', 'seller'));
    }

    public function store(Listing $listing, Request $request)
    {
        $result = $this->orderService->createOrder(
            $listing,
            Auth::user(),
            $request->only(['shipping_cost', 'idempotency_key'])
        );

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('marketplace.orders.show', $result['order']->id)
            ->with('success', 'Order placed successfully! Payment is held in escrow.');
    }

    public function confirmReceipt($id)
    {
        $order = \App\Models\Marketplace\Order::findOrFail($id);
        $result = $this->orderService->confirmReceipt($order, Auth::user());

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('marketplace.orders.show', $order->id)
            ->with('success', 'Order confirmed! Funds released to seller.');
    }

    public function cancel($id)
    {
        $order = \App\Models\Marketplace\Order::findOrFail($id);
        $result = $this->orderService->cancelOrder($order, Auth::user());

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('marketplace.orders.index')
            ->with('success', 'Order cancelled and funds refunded.');
    }

    public function mySales()
    {
        $orders = \App\Models\Marketplace\Order::query()
            ->with(['listing', 'buyer', 'escrow'])
            ->where('seller_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('marketplace.orders.sales', compact('orders'));
    }

    public function markAsShipped($id)
    {
        $order = \App\Models\Marketplace\Order::findOrFail($id);
        $result = $this->orderService->markAsShipped($order, Auth::user());

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', 'Order marked as shipped.');
    }

    public function markAsDelivered($id)
    {
        $order = \App\Models\Marketplace\Order::findOrFail($id);
        $result = $this->orderService->markAsDelivered($order, Auth::user());

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', 'Order marked as delivered.');
    }
}