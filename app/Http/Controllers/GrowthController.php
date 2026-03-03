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
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'order' => $result['order'],
            'redirect' => route('growth.orders.show', $result['order']->id),
        ]);
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

            // Use the Notification model's sendTo method
            \App\Models\Notification::sendTo(
                $recipient,
                'New Message from ' . $sender->name,
                "Subject: {$validated['subject']}\n\n{$validated['message']}",
                'contact_message',
                [
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->name,
                    'action_url' => route('growth.index'),
                ]
            );

            $conversation = MarketplaceConversation::findOrCreate(
                'growth_service',
                0,
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
