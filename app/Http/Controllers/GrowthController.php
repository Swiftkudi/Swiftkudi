<?php

namespace App\Http\Controllers;

use App\Models\GrowthListing;
use App\Models\GrowthOrder;
use App\Models\MarketplaceConversation;
use App\Models\MarketplaceMessage;
use App\Models\MarketplaceCategory;
use App\Services\GrowthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GrowthController extends Controller
{
    protected $service;

    public function __construct(GrowthService $service)
    {
        $this->service = $service;
    }

    /**
     * Browse listings by type
     */
    public function index(Request $request, ?string $type = null): View
    {
        $query = GrowthListing::active()->with('seller');

        // Get available types based on buyer categories
        $availableTypes = null;
        $categorySlugs = [];
        $user = auth()->user();
        if ($user && $user->account_type === 'buyer' && $user->buyer_onboarding_completed) {
            $buyerCategories = $user->getBuyerCategories();
            if (!empty($buyerCategories)) {
                // Get selected categories from marketplace - use slug as type
                $availableTypes = MarketplaceCategory::whereIn('id', $buyerCategories)
                    ->where('type', 'growth')
                    ->get();
                
                // Collect slugs for filtering listings
                $categorySlugs = $availableTypes->pluck('slug')->toArray();
            }
        }

        // Filter by buyer-selected categories if any
        if (!empty($categorySlugs)) {
            $query->whereIn('type', $categorySlugs);
        }

        if ($type) {
            $query->ofType($type);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $listings = $query->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $types = $availableTypes;
        

        return view('growth.index', compact('listings', 'types', 'type'));
    }

    /**
     * Show listing details
     */
    public function show(int $id): View
    {
        $listing = GrowthListing::with('seller')->findOrFail($id);
        
        $specsFields = GrowthService::getSpecsFields($listing->type);

        return view('growth.show', compact('listing', 'specsFields'));
    }

    /**
     * Show create listing form
     */
    public function create(Request $request): View
    {
        $type = $request->get('type', 'backlinks');
        $specsFields = GrowthService::getSpecsFields($type);

        $types = [
            'backlinks' => 'Backlinks',
            'influencer' => 'Influencer Promotion',
            'newsletter' => 'Newsletter',
            'leads' => 'Lead Generation',
        ];

        return view('growth.create', compact('type', 'specsFields', 'types'));
    }

    /**
     * Store new listing
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:backlinks,influencer,newsletter,leads',
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20|max:5000',
            'price' => 'required|numeric|min:100',
            'delivery_days' => 'required|integer|min:1|max:30',
            'specs' => 'nullable|array',
        ]);

        $user = Auth::user();
        $result = $this->service->createListing($user, $validated);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        app(\App\Services\NotificationDispatchService::class)->sendToUser(
            $user,
            'Growth Listing Created',
            'Your listing "' . ($result['listing']->title ?? $validated['title']) . '" has been submitted successfully.',
            \App\Models\Notification::TYPE_SYSTEM,
            ['listing_id' => $result['listing']->id ?? null, 'action_url' => route('growth.my-listings')],
            'notify_growth_orders',
            true
        );

        if ($user->account_type === 'growth_seller' && !$user->growth_listing_created) {
            // Use centralized service for unlock logic
            app(\App\Services\TaskGateProgressService::class)->unlockMarketplaceSeller(
                $user,
                'growth_seller'
            );
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'redirect' => route('growth.my-listings'),
        ]);
    }

    /**
     * My listings
     */
    public function myListings(): View
    {
        $user = Auth::user();
        
        $listings = GrowthListing::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('growth.my-listings', compact('listings'));
    }

    /**
     * Show edit form
     */
    public function edit(int $id): View
    {
        $listing = GrowthListing::where('user_id', auth()->id())->findOrFail($id);
        
        $specsFields = GrowthService::getSpecsFields($listing->type);

        return view('growth.edit', compact('listing', 'specsFields'));
    }

    /**
     * Update listing
     */
    public function update(Request $request, int $id)
    {
        $listing = GrowthListing::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20|max:5000',
            'price' => 'required|numeric|min:100',
            'delivery_days' => 'required|integer|min:1|max:30',
            'specs' => 'nullable|array',
        ]);

        $listing->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Listing updated successfully',
        ]);
    }

    /**
     * Delete listing
     */
    public function destroy(int $id)
    {
        $listing = GrowthListing::where('user_id', auth()->id())->findOrFail($id);
        
        $listing->update(['status' => GrowthListing::STATUS_DELETED]);

        return response()->json([
            'success' => true,
            'message' => 'Listing deleted successfully',
        ]);
    }

    /**
     * Start conversation about a listing
     */
    public function startConversation(Request $request, int $id)
    {
        $request->validate([
            'message' => 'required|string|min:1|max:2000',
        ]);

        $listing = GrowthListing::active()->findOrFail($id);
        $seller = $listing->seller;
        $buyer = auth()->user();

        // Don't allow messaging yourself
        if ($seller->id === $buyer->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot message yourself',
            ], 400);
        }

        // Check for existing conversation
        $conversation = MarketplaceConversation::where('growth_listing_id', $listing->id)
            ->where('buyer_id', $buyer->id)
            ->where('seller_id', $seller->id)
            ->first();

        if (!$conversation) {
            $conversation = MarketplaceConversation::create([
                'growth_listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
            ]);
        }

        // Add message
        MarketplaceMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'message' => $request->message,
        ]);

        // Notify seller
        app(\App\Services\NotificationDispatchService::class)->sendToUser(
            $seller,
            'New Message on ' . $listing->title,
            'You have a new message from ' . $buyer->name . ' about your growth listing.',
            \App\Models\Notification::TYPE_SYSTEM,
            ['conversation_id' => $conversation->id, 'action_url' => route('messages.show', $conversation->id)],
            'notify_growth_orders'
        );

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
        ]);
    }

    /**
     * Get specs fields for a type
     */
    public function getSpecsFields(Request $request)
    {
        $type = $request->get('type', 'backlinks');
        
        $specsFields = GrowthService::getSpecsFields($type);

        return response()->json([
            'success' => true,
            'specs_fields' => $specsFields,
        ]);
    }
}
