<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\ReviewService;
use App\Models\Marketplace\MarketplaceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct()
    {
        $this->reviewService = app(ReviewService::class);
    }

    public function create(MarketplaceOrder $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($order->status !== MarketplaceOrder::STATUS_DELIVERED) {
            abort(403, 'Order must be delivered before leaving a review');
        }

        // Check if review already exists
        $existingReview = $order->reviews()
            ->where('reviewer_id', Auth::id())
            ->first();

        if ($existingReview) {
            return redirect()->route('marketplace.orders.show', $order->id)
                ->with('info', 'You have already reviewed this order.');
        }

        return view('marketplace.reviews.create', compact('order'));
    }

    public function store(MarketplaceOrder $order, Request $request)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $images[] = $file->store('marketplace/reviews/' . Auth::id(), 'public');
            }
            $validated['images'] = $images;
        }

        $review = $this->reviewService->submitReview($order, Auth::user(), $validated);

        return redirect()->route('marketplace.orders.show', $order->id)
            ->with('success', 'Review submitted successfully!');
    }

    public function index()
    {
        $reviews = \App\Models\Marketplace\MarketplaceReview::query()
            ->with(['order', 'reviewer', 'reviewed'])
            ->where('reviewer_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('marketplace.reviews.index', compact('reviews'));
    }
}