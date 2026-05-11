<?php

namespace App\Http\Controllers;

use App\Models\GrowthListing;
use App\Models\GrowthOrder;
use App\Models\MarketplaceConversation;
use App\Models\MarketplaceMessage;
use App\Models\MarketplaceCategory;
use App\Services\GrowthService;
use App\Services\NotificationManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GrowthController extends Controller
{
    protected $service;
    protected NotificationManager $notificationManager;

    public function __construct(GrowthService $service, NotificationManager $notificationManager)
    {
        $this->service = $service;
        $this->notificationManager = $notificationManager;
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
        if ($user && $user->account_type === 'buyer') {
            // Check if buyer onboarding is enabled and category selection is required
            if (\App\Services\OnboardingSettingsService::isBuyerOnboardingEnabled() &&
                \App\Services\OnboardingSettingsService::isBuyerCategorySelectionRequired() &&
                $user->buyer_onboarding_completed) {

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

        // Get available types for filtering - use default types if buyer has no specific categories
        $user = auth()->user();
        if (!$availableTypes || $availableTypes->isEmpty()) {
            $types = [
                'backlinks' => (object)['slug' => 'backlinks', 'name' => 'Backlinks'],
                'influencer' => (object)['slug' => 'influencer', 'name' => 'Influencer'],
                'newsletter' => (object)['slug' => 'newsletter', 'name' => 'Newsletter'],
                'leads' => (object)['slug' => 'leads', 'name' => 'Leads'],
            ];
        } else {
            $types = $availableTypes->keyBy('slug');
        }
        

        return view('growth.index', compact('listings', 'types', 'type'));
    }

    /**
     * Show listing details
     */
    public function show(string $listing): View
    {
        $growthListing = GrowthListing::with('seller')->findOrFail($listing);
        
        $specsFields = GrowthService::getSpecsFields($growthListing->type);
        
        return view('growth.show', compact('growthListing', 'specsFields'));
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

        $this->notificationManager->notify(
            NotificationManager::EVENT_GROWTH_LISTING_CREATED,
            $user,
            [
                'listing_id' => $result['listing']->id ?? null,
                'listing_title' => $result['listing']->title ?? $validated['title'],
                'action_url' => route('growth.my-listings'),
            ]
        );

        if ($user->account_type === 'growth_seller' && !$user->growth_listing_created) {
            // Use centralized service for unlock logic
            app(\App\Services\TaskGateProgressService::class)->unlockMarketplaceSeller(
                $user,
                'growth_seller'
            );
        }

        // Check for next onboarding step after listing creation
        $nextStep = null;
        if ($user->account_type === 'growth_seller' && $user->growth_listing_created) {
            $user->refresh(); // Refresh to get updated fields
            $nextStep = app(\App\Services\AccessGateService::class)->getNextOnboardingStep($user);
        }

        $response = [
            'success' => true,
            'message' => $result['message'],
            'redirect' => route('growth.my-listings'),
        ];

        if ($nextStep) {
            $response['next_step_redirect'] = route($nextStep['route']);
            $response['next_step_message'] = $nextStep['message'];
        }

        return response()->json($response);
    }

    /**
     * My listings
     */
    public function myListings(): View
    {
        $user = Auth::user();
        
        $activeListings = GrowthListing::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $pendingListings = GrowthListing::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('growth.my-listings', compact('activeListings', 'pendingListings'));
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
        $this->notificationManager->notify(
            NotificationManager::EVENT_GROWTH_MESSAGE_RECEIVED,
            $seller,
            [
                'conversation_id' => $conversation->id,
                'listing_title' => $listing->title,
                'sender_name' => $buyer->name,
                'action_url' => route('messages.show', $conversation->id),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
        ]);
    }

/**
     * Get specs fields for a type
     */
    public static function getSpecsFields(string $type): array
    {
        $fields = [];
        
        switch($type) {
            case GrowthListing::TYPE_BACKLINKS:
                $fields = [
                    ['name' => 'website_url', 'label' => 'Website URL', 'type' => 'url', 'required' => true],
                    ['name' => 'niche', 'label' => 'Niche', 'type' => 'text', 'required' => true],
                    ['name' => 'traffic', 'label' => 'Monthly Traffic', 'type' => 'text', 'required' => false],
                    ['name' => 'domain_authority', 'label' => 'Domain Authority (DA)', 'type' => 'number', 'required' => false],
                    ['name' => 'link_type', 'label' => 'Link Type', 'type' => 'select', 'options' => ['Dofollow', 'Nofollow', 'Both'], 'required' => true],
                ];
                break;
                
            case GrowthListing::TYPE_INFLUENCER:
                $fields = [
                    ['name' => 'platform', 'label' => 'Platform', 'type' => 'select', 'options' => ['Instagram', 'TikTok', 'YouTube', 'Twitter', 'Facebook', 'Other'], 'required' => true],
                    ['name' => 'followers', 'label' => 'Followers Count', 'type' => 'number', 'required' => true],
                    ['name' => 'engagement_rate', 'label' => 'Engagement Rate (%)', 'type' => 'number', 'required' => false],
                    ['name' => 'audience_country', 'label' => 'Primary Audience Country', 'type' => 'text', 'required' => false],
                    ['name' => 'promotion_type', 'label' => 'Promotion Type', 'type' => 'select', 'options' => ['Story', 'Post', 'Reel', 'Video', 'Other'], 'required' => true],
                ];
                break;
                
            case GrowthListing::TYPE_NEWSLETTER:
                $fields = [
                    ['name' => 'subscriber_count', 'label' => 'Subscriber Count', 'type' => 'number', 'required' => true],
                    ['name' => 'open_rate', 'label' => 'Open Rate (%)', 'type' => 'number', 'required' => false],
                    ['name' => 'niche', 'label' => 'Niche/Category', 'type' => 'text', 'required' => true],
                ];
                break;
                
            case GrowthListing::TYPE_LEADS:
                $fields = [
                    ['name' => 'lead_type', 'label' => 'Lead Type', 'type' => 'select', 'options' => ['Email', 'Phone', 'Company', 'B2B', 'B2C', 'Other'], 'required' => true],
                    ['name' => 'target_country', 'label' => 'Target Country', 'type' => 'text', 'required' => true],
                    ['name' => 'min_quantity', 'label' => 'Minimum Quantity', 'type' => 'number', 'required' => true],
                ];
                break;
        }
        
        return $fields;
    }

    /**
     * Create order (buyer)
     */
    public function createOrder(Request $request, int $id)
    {
        $user = Auth::user();
        $result = $this->service->createOrder($user, $id);

        if (!$result['success']) {
            if (isset($result['required'], $result['available'])) {
                $requiredTopup = max(0, (float) $result['required'] - (float) $result['available']);
                session([
                    'pending_growth_checkout' => [
                        'listing_id' => $id,
                    ],
                    'deposit_success_redirect' => route('growth.checkout.resume'),
                    'insufficient_balance_required' => $requiredTopup,
                ]);

                $result['redirect'] = route('wallet.deposit', ['required' => $requiredTopup]);
                $result['message'] = 'Insufficient wallet balance. Deposit and you will be returned to complete this order.';
            }

            return response()->json($result, 400);
        }

        // Notify seller about new order
        $this->notificationManager->notify(
            NotificationManager::EVENT_GROWTH_ORDER_CREATED,
            $result['order']->seller,
            [
                'order_id' => $result['order']->id,
                'listing_title' => $result['order']->listing->title,
                'buyer_name' => $user->name,
            ]
        );
        $result['redirect']=route('growth.orders.show', $result['order']->id);

        return response()->json($result);
    }

    /**
     * Resume growth checkout after successful deposit
     */
    public function resumeCheckout(Request $request)
    {
        $pending = session('pending_growth_checkout');

        if (!$pending || empty($pending['listing_id'])) {
            return redirect()->route('growth.index')->with('error', 'No pending growth checkout found to resume.');
        }

        $result = $this->service->createOrder(
            Auth::user(),
            (int) $pending['listing_id']
        );

        if (!$result['success']) {
            if (isset($result['required'], $result['available'])) {
                $requiredTopup = max(0, (float) $result['required'] - (float) $result['available']);
                session([
                    'deposit_success_redirect' => route('growth.checkout.resume'),
                    'insufficient_balance_required' => $requiredTopup,
                ]);

                return redirect()
                    ->route('wallet.deposit', ['required' => $requiredTopup])
                    ->with('error', 'Your balance is still insufficient. Please complete your deposit to continue.');
            }

            return redirect()->route('growth.show', (int) $pending['listing_id'])
                ->with('error', $result['message'] ?? 'Failed to resume growth checkout.');
        }

        // Notify seller about new order
        $this->notificationManager->notify(
            NotificationManager::EVENT_GROWTH_ORDER_CREATED,
            $result['order']->seller,
            [
                'order_id' => $result['order']->id,
                'listing_title' => $result['order']->listing->title,
                'buyer_name' => Auth::user()->name,
            ]
        );

        session()->forget(['pending_growth_checkout', 'deposit_success_redirect', 'insufficient_balance_required']);

        return redirect()->route('growth.orders.show', $result['order']->id)
            ->with('success', 'Growth order completed successfully after deposit.');
    }

    /**
     * My orders (buyer)
     */



    /**
     * My orders (buyer)
     */
    public function myOrders(): View
    {
        $user = Auth::user();
        
        $activeOrders = GrowthOrder::forBuyer($user->id)
            ->whereIn('status', ['paid', 'in_progress', 'delivered', 'revision'])
            ->with('listing')
            ->orderBy('created_at', 'desc')
            ->get();

        $completedOrders = GrowthOrder::forBuyer($user->id)
            ->where('status', 'completed')
            ->with('listing')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        return view('growth.orders.index', compact('activeOrders', 'completedOrders'));
    }

    /**
     * Sales (seller)
     */
    public function mySales(): View
    {
        $user = Auth::user();
        
        $activeSales = GrowthOrder::forSeller($user->id)
            ->whereIn('status', ['paid', 'in_progress', 'delivered', 'revision'])
            ->with('listing', 'buyer')
            ->orderBy('created_at', 'desc')
            ->get();

        $completedSales = GrowthOrder::forSeller($user->id)
            ->where('status', 'completed')
            ->with('listing', 'buyer')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        return view('growth.sales.index', compact('activeSales', 'completedSales'));
    }

    /**
     * Show order details
     */
    public function showOrder(int $id): View
    {
        $order = GrowthOrder::with(['listing', 'buyer', 'seller'])->findOrFail($id);

        if ($order->buyer_id !== Auth::id() && $order->seller_id !== Auth::id()) {
            abort(403);
        }

        return view('growth.orders.show', compact('order'));
    }

    /**
     * Submit proof (seller)
     */
    public function submitProof(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'proof_data' => 'nullable|array',
            'notes' => 'required|string|min:10',
        ]);

        $order = GrowthOrder::findOrFail($orderId);
        $user = Auth::user();

        $result = $this->service->submitProof(
            $order,
            $user,
            $validated['proof_data'] ?? [],
            $validated['notes']
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Approve proof (buyer)
     */
    public function approveOrder(int $orderId)
    {
        $order = GrowthOrder::findOrFail($orderId);
        $user = Auth::user();

        $result = $this->service->approveProof($order, $user);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Request revision (buyer)
     */
    public function requestRevision(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'notes' => 'required|string|min:10',
        ]);

        $order = GrowthOrder::findOrFail($orderId);
        $user = Auth::user();

        $result = $this->service->requestRevision($order, $user, $validated['notes']);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Cancel order
     */
    public function cancelOrder(int $orderId)
    {
        $order = GrowthOrder::findOrFail($orderId);
        $user = Auth::user();

        $result = $this->service->cancelOrder($order, $user);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
