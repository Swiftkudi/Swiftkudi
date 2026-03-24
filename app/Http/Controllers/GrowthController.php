<?php

namespace App\Http\Controllers;

use App\Models\GrowthListing;
use App\Models\GrowthOrder;
use App\Models\MarketplaceConversation;
use App\Models\MarketplaceMessage;
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
    public function index(Request $request, string $type = null): View
    {
        $query = GrowthListing::active()->with('seller');

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

        $types = [
            'backlinks' => 'Backlinks',
            'influencer' => 'Influencers',
            'newsletter' => 'Newsletters',
            'leads' => 'Leads',
        ];

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
        
        $activeListings = GrowthListing::where('user_id', $user->id)
            ->where('status', GrowthListing::STATUS_ACTIVE)
            ->get();

        $pendingListings = GrowthListing::where('user_id', $user->id)
            ->where('status', GrowthListing::STATUS_PENDING)
            ->get();

        return view('growth.my-listings', compact('activeListings', 'pendingListings'));
    }

    /**
     * Create order
     */
    public function createOrder(Request $request, int $listingId)
    {
        $user = Auth::user();
        $result = $this->service->createOrder($user, $listingId);

        if (!$result['success']) {
            if (isset($result['required'], $result['available'])) {
                $requiredTopup = max(0, (float) $result['required'] - (float) $result['available']);
                session([
                    'pending_growth_checkout' => [
                        'listing_id' => $listingId,
                    ],
                    'deposit_success_redirect' => route('growth.checkout.resume'),
                    'insufficient_balance_required' => $requiredTopup,
                ]);

                $result['redirect'] = route('wallet.deposit', ['required' => $requiredTopup]);
                $result['message'] = 'Insufficient wallet balance. Deposit and you will be returned to complete this order.';
            }

            return response()->json($result, 400);
        }

        try {
            $conversation = MarketplaceConversation::findOrCreate(
                'growth_service',
                $result['order']->listing_id,
                $result['order']->buyer_id,
                $result['order']->seller_id
            );

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $result['order']->seller,
                'New Growth Order Received',
                'You received a new growth order for "' . ($result['order']->listing->title ?? 'Growth Listing') . '".',
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $result['order']->id, 'action_url' => route('growth.orders.show', $result['order']->id)],
                'notify_growth_orders',
                true
            );

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $result['order']->buyer,
                'Growth Order Confirmed',
                'Your order for "' . ($result['order']->listing->title ?? 'Growth Listing') . '" has been placed successfully.',
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $result['order']->id, 'action_url' => route('growth.orders.show', $result['order']->id)],
                'notify_growth_orders'
            );

            MarketplaceMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $result['order']->buyer_id,
                'message' => 'New order placed for "' . ($result['order']->listing->title ?? 'Growth Listing') . '". Please review and begin delivery.',
                'is_read' => false,
            ]);

            $conversation->update(['last_message_at' => now()]);
        } catch (\Exception $e) {
            Log::warning('Failed to create growth order conversation', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'order' => $result['order'],
            'redirect' => route('growth.orders.show', $result['order']->id),
        ]);
    }

    /**
     * Resume growth checkout after successful deposit
     */
    public function resumeCheckout(Request $request)
    {
        $pending = session('pending_growth_checkout');

        if (!$pending || empty($pending['listing_id'])) {
            return redirect()->route('growth.index')->with('error', 'No pending growth order found to resume.');
        }


        $result = $this->service->createOrder(Auth::user(), (int) $pending['listing_id']);

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

        try {
            $conversation = MarketplaceConversation::findOrCreate(
                'growth_service',
                $result['order']->listing_id,
                $result['order']->buyer_id,
                $result['order']->seller_id
            );

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $result['order']->seller,
                'Growth Checkout Resumed',
                'A buyer resumed checkout and confirmed order for "' . ($result['order']->listing->title ?? 'Growth Listing') . '".',
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $result['order']->id, 'action_url' => route('growth.orders.show', $result['order']->id)],
                'notify_growth_orders',
                true
            );

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $result['order']->buyer,
                'Growth Order Confirmed',
                'Your order for "' . ($result['order']->listing->title ?? 'Growth Listing') . '" has been placed successfully.',
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $result['order']->id, 'action_url' => route('growth.orders.show', $result['order']->id)],
                'notify_growth_orders'
            );

            MarketplaceMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $result['order']->buyer_id,
                'message' => 'Checkout resumed and order confirmed for "' . ($result['order']->listing->title ?? 'Growth Listing') . '".',
                'is_read' => false,
            ]);

            $conversation->update(['last_message_at' => now()]);
        } catch (\Exception $e) {
            Log::warning('Failed to create growth resume conversation', ['error' => $e->getMessage()]);
        }

        session()->forget(['pending_growth_checkout', 'deposit_success_redirect', 'insufficient_balance_required']);

        return redirect()->route('growth.orders.show', $result['order']->id)
            ->with('success', 'Growth order completed successfully after deposit.');
    }

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
     * My sales (seller)
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

        // Verify access
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
            'proof_data' => 'required|array',
            'notes' => 'required|string|min:10',
        ]);

        $order = GrowthOrder::findOrFail($orderId);
        $user = Auth::user();

        $result = $this->service->submitProof($order, $user, $validated['proof_data'], $validated['notes']);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Approve order (buyer)
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

    /**
     * Contact a seller
     */
    public function contact(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'listing_id' => 'nullable|exists:growth_listings,id',
            'subject' => 'required|string|min:3|max:255',
            'message' => 'required|string|min:10|max:5000',
        ]);

        $sender = Auth::user();
        $recipientId = $validated['recipient_id'];

        // Prevent sending message to yourself
        if ($sender->id == $recipientId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot send a message to yourself.',
            ], 400);
        }

        try {
            $recipient = \App\Models\User::find($recipientId);
            
            if (!$recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recipient not found.',
                ], 404);
            }

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $recipient,
                'New Message from ' . $sender->name,
                "Subject: {$validated['subject']}\n\n{$validated['message']}",
                \App\Models\Notification::TYPE_SYSTEM,
                [
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->name,
                    'action_url' => route('growth.index'),
                ],
                'notify_chat_messages',
                true
            );

            $conversation = MarketplaceConversation::findOrCreate(
                'growth_service',
                (int) ($validated['listing_id'] ?? 0),
                $sender->id,
                $recipient->id
            );

            MarketplaceMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'message' => "Subject: {$validated['subject']}\n\n{$validated['message']}",
                'is_read' => false,
            ]);

            $conversation->update(['last_message_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully!',
                'chat_url' => route('chat.show', $conversation),
            ]);
        } catch (\Exception $e) {
            Log::error('Contact seller error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ], 500);
        }
    }
}
