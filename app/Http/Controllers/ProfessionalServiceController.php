<?php

namespace App\Http\Controllers;

use App\Models\ProfessionalService;
use App\Models\ProfessionalServiceCategory;
use App\Models\ProfessionalServiceMessage;
use App\Models\ProfessionalServiceOrder;
use App\Models\MarketplaceConversation;
use App\Models\MarketplaceMessage;
use App\Models\ServiceProviderProfile;
use App\Services\ProfessionalServiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProfessionalServiceController extends Controller
{
    protected $service;

    public function __construct(ProfessionalServiceService $service)
    {
        $this->service = $service;
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Browse services (public)
     */
    public function index(Request $request): View
    {
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;

        $query = ProfessionalService::active()
            ->with(['category', 'seller']);

        // Add buyer category filter
        $user = auth()->user();
        if ($user && $user->account_type === 'buyer' && $user->buyer_onboarding_completed) {
            $buyerCategories = $user->getBuyerCategories();
            if (!empty($buyerCategories)) {
                $query->whereIn('category_id', $buyerCategories);
            }
        }

        if ($request->category) {
            $query->ofCategory($request->category);
        }

        if ($request->search) {
            $query->search($request->search);
        }

        $services = $query->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        $categories = ProfessionalServiceCategory::active()->get();

        return view('professional-services.index', compact('services', 'categories'));
    }

    /**
     * Show service details
     */
    public function show(int $id): View
    {
        $service = ProfessionalService::with(['category', 'seller', 'addons'])
            ->findOrFail($id);

        $userHasOrder = false;
        if (Auth::check()) {
            $userHasOrder = ProfessionalServiceOrder::where('service_id', $id)
                ->where('buyer_id', Auth::id())
                ->exists();
        }

        return view('professional-services.show', compact('service', 'userHasOrder'));
    }

    /**
     * Show create service form
     */
    public function create(): View
    {
        $settings = $this->service->getSettings();
        $categories = $settings['categories'];
        
        return view('professional-services.create', compact('categories', 'settings'));
    }

    /**
     * Store new service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20|max:5000',
            'category_id' => 'required|exists:professional_service_categories,id',
            'price' => 'required|numeric|min:100',
            'delivery_days' => 'required|integer|min:1|max:30',
            'revisions_included' => 'required|integer|min:0|max:5',
            'portfolio_links' => 'nullable|array',
            'addons' => 'nullable|array',
            'addons.*.name' => 'required|string',
            'addons.*.price' => 'required|numeric|min:0',
            'addons.*.delivery_days_extra' => 'nullable|integer|min:0',
        ]);

        $user = Auth::user();
        $result = $this->service->createService($user, $validated);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        app(\App\Services\NotificationDispatchService::class)->sendToUser(
            $user,
            'Service Created',
            'Your service "' . ($result['service']->title ?? $validated['title']) . '" has been submitted successfully.',
            \App\Models\Notification::TYPE_SYSTEM,
            ['service_id' => $result['service']->id ?? null, 'action_url' => route('professional-services.my-services')],
            'notify_service_orders',
            true
        );

        if ($user->account_type === 'freelancer' && !$user->freelancer_service_created) {
            // Use centralized service for unlock logic
            app(\App\Services\TaskGateProgressService::class)->unlockMarketplaceSeller(
                $user,
                'freelancer'
            );
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'redirect' => route('professional-services.my-services'),
        ]);
    }

    /**
     * My services (seller view)
     */
    public function myServices(): View
    {
        $user = Auth::user();
        
        $activeServices = ProfessionalService::where('user_id', $user->id)
            ->where('status', ProfessionalService::STATUS_ACTIVE)
            ->with('category')
            ->get();

        $pendingServices = ProfessionalService::where('user_id', $user->id)
            ->where('status', ProfessionalService::STATUS_PENDING)
            ->with('category')
            ->get();

        $draftServices = ProfessionalService::where('user_id', $user->id)
            ->where('status', ProfessionalService::STATUS_DRAFT)
            ->with('category')
            ->get();

        return view('professional-services.my-services', compact(
            'activeServices', 'pendingServices', 'draftServices'
        ));
    }

    /**
     * Create order / checkout
     */
    public function createOrder(Request $request, int $serviceId)
    {
        $validated = $request->validate([
            'addon_ids' => 'nullable|array',
            'addon_ids.*' => 'integer',
            'requirements' => 'required|string|min:10',
        ]);

        $user = Auth::user();
        $result = $this->service->createOrder(
            $user, 
            $serviceId, 
            $validated['addon_ids'] ?? [],
            $validated['requirements']
        );

        if (!$result['success']) {
            if (isset($result['required'], $result['available'])) {
                $requiredTopup = max(0, (float) $result['required'] - (float) $result['available']);
                session([
                    'pending_service_checkout' => [
                        'service_id' => $serviceId,
                        'addon_ids' => $validated['addon_ids'] ?? [],
                        'requirements' => $validated['requirements'],
                    ],
                    'deposit_success_redirect' => route('professional-services.checkout.resume'),
                    'insufficient_balance_required' => $requiredTopup,
                ]);

                $result['redirect'] = route('wallet.deposit', ['required' => $requiredTopup]);
                $result['message'] = 'Insufficient wallet balance. Deposit and you will be returned to complete this order.';
            }

            return response()->json($result, 400);
        }

        try {
            $conversation = MarketplaceConversation::findOrCreate(
                'professional_service',
                $result['order']->service_id,
                $result['order']->buyer_id,
                $result['order']->seller_id
            );

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $result['order']->seller,
                'New Service Order Received',
                'You received a new order for "' . ($result['order']->service->title ?? 'Professional Service') . '".',
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $result['order']->id, 'action_url' => route('professional-services.orders.show', $result['order']->id)],
                'notify_service_orders',
                true
            );

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $result['order']->buyer,
                'Service Order Confirmed',
                'Your order for "' . ($result['order']->service->title ?? 'Professional Service') . '" has been placed successfully.',
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $result['order']->id, 'action_url' => route('professional-services.orders.show', $result['order']->id)],
                'notify_service_orders'
            );

            MarketplaceMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $result['order']->buyer_id,
                'message' => 'New order placed for "' . ($result['order']->service->title ?? 'Professional Service') . '". Requirements: ' . ($result['order']->requirements ?? 'N/A'),
                'is_read' => false,
            ]);

            $conversation->update(['last_message_at' => now()]);
        } catch (\Exception $e) {
            Log::warning('Failed to create professional order conversation', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'order' => $result['order'],
            'redirect' => route('professional-services.orders.show', $result['order']->id),
        ]);
    }

    /**
     * Resume professional service checkout after successful deposit
     */
    public function resumeCheckout(Request $request)
    {
        $pending = session('pending_service_checkout');

        if (!$pending || empty($pending['service_id'])) {
            return redirect()->route('professional-services.index')->with('error', 'No pending service checkout found to resume.');
        }

        $result = $this->service->createOrder(
            Auth::user(),
            (int) $pending['service_id'],
            (array) ($pending['addon_ids'] ?? []),
            (string) ($pending['requirements'] ?? '')
        );

        if (!$result['success']) {
            if (isset($result['required'], $result['available'])) {
                $requiredTopup = max(0, (float) $result['required'] - (float) $result['available']);
                session([
                    'deposit_success_redirect' => route('professional-services.checkout.resume'),
                    'insufficient_balance_required' => $requiredTopup,
                ]);

                return redirect()
                    ->route('wallet.deposit', ['required' => $requiredTopup])
                    ->with('error', 'Your balance is still insufficient. Please complete your deposit to continue.');
            }

            return redirect()->route('professional-services.show', (int) $pending['service_id'])
                ->with('error', $result['message'] ?? 'Failed to resume service checkout.');
        }

        try {
            $conversation = MarketplaceConversation::findOrCreate(
                'professional_service',
                $result['order']->service_id,
                $result['order']->buyer_id,
                $result['order']->seller_id
            );

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $result['order']->seller,
                'Service Checkout Resumed',
                'A buyer resumed checkout and confirmed order for "' . ($result['order']->service->title ?? 'Professional Service') . '".',
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $result['order']->id, 'action_url' => route('professional-services.orders.show', $result['order']->id)],
                'notify_service_orders',
                true
            );

            MarketplaceMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $result['order']->buyer_id,
                'message' => 'Checkout resumed and order confirmed for "' . ($result['order']->service->title ?? 'Professional Service') . '".',
                'is_read' => false,
            ]);

            $conversation->update(['last_message_at' => now()]);
        } catch (\Exception $e) {
            Log::warning('Failed to create professional resume conversation', ['error' => $e->getMessage()]);
        }

        session()->forget(['pending_service_checkout', 'deposit_success_redirect', 'insufficient_balance_required']);

        return redirect()->route('professional-services.orders.show', $result['order']->id)
            ->with('success', 'Service order completed successfully after deposit.');
    }

    /**
     * My orders (buyer)
     */
    public function myOrders(): View
    {
        $user = Auth::user();
        
        $activeOrders = ProfessionalServiceOrder::forBuyer($user->id)
            ->whereIn('status', ['paid', 'in_progress', 'delivered', 'revision'])
            ->with('service')
            ->orderBy('created_at', 'desc')
            ->get();

        $completedOrders = ProfessionalServiceOrder::forBuyer($user->id)
            ->where('status', 'completed')
            ->with('service')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        return view('professional-services.orders.index', compact('activeOrders', 'completedOrders'));
    }

    /**
     * Sales (seller)
     */
    public function mySales(): View
    {
        $user = Auth::user();
        
        $activeSales = ProfessionalServiceOrder::forSeller($user->id)
            ->whereIn('status', ['paid', 'in_progress', 'delivered', 'revision'])
            ->with('service', 'buyer')
            ->orderBy('created_at', 'desc')
            ->get();

        $completedSales = ProfessionalServiceOrder::forSeller($user->id)
            ->where('status', 'completed')
            ->with('service', 'buyer')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        return view('professional-services.sales.index', compact('activeSales', 'completedSales'));
    }

    /**
     * Show order details
     */
    public function showOrder(int $id): View
    {
        $order = ProfessionalServiceOrder::with(['service', 'buyer', 'seller', 'messages.sender'])
            ->findOrFail($id);

        // Verify access
        if ($order->buyer_id !== Auth::id() && $order->seller_id !== Auth::id()) {
            abort(403);
        }

        return view('professional-services.orders.show', compact('order'));
    }

    /**
     * Deliver order (seller)
     */
    public function deliverOrder(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'notes' => 'required|string|min:10',
            'files' => 'nullable|array',
        ]);

        $order = ProfessionalServiceOrder::findOrFail($orderId);
        $user = Auth::user();

        $result = $this->service->deliverOrder(
            $order, 
            $user, 
            $validated['notes'],
            $validated['files'] ?? []
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Approve delivery (buyer)
     */
    public function approveOrder(int $orderId)
    {
        request()->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        $order = ProfessionalServiceOrder::findOrFail($orderId);
        $user = Auth::user();

        $result = $this->service->approveDeliveryWithReview(
            $order,
            $user,
            (int) request('rating'),
            (string) request('comment')
        );

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

        $order = ProfessionalServiceOrder::findOrFail($orderId);
        $user = Auth::user();

        $result = $this->service->requestRevision($order, $user, $validated['notes']);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Cancel order
     */
    public function cancelOrder(int $orderId)
    {
        $order = ProfessionalServiceOrder::findOrFail($orderId);
        $user = Auth::user();

        $result = $this->service->cancelOrder($order, $user);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Add message to order
     */
    public function sendMessage(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:1',
            'attachments' => 'nullable|array',
        ]);

        $order = ProfessionalServiceOrder::findOrFail($orderId);
        
        // Verify access
        if ($order->buyer_id !== Auth::id() && $order->seller_id !== Auth::id()) {
            abort(403);
        }

        $message = ProfessionalServiceMessage::create([
            'order_id' => $orderId,
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
            'attachments' => $validated['attachments'] ?? [],
        ]);

        $conversation = MarketplaceConversation::findOrCreate(
            'professional_service',
            $order->service_id,
            $order->buyer_id,
            $order->seller_id
        );

        MarketplaceMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'conversation_id' => $conversation->id,
        ]);
    }

    /**
     * Leave review
     */
    public function leaveReview(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10',
        ]);

        $order = ProfessionalServiceOrder::findOrFail($orderId);
        $user = Auth::user();

        $result = $this->service->createReview(
            $order, 
            $user, 
            $validated['rating'], 
            $validated['comment']
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Service provider profile
     */
    public function providerProfile(int $userId): View
    {
        $profile = ServiceProviderProfile::with('user')
            ->where('user_id', $userId)
            ->firstOrFail();

        $services = ProfessionalService::where('user_id', $userId)
            ->where('status', ProfessionalService::STATUS_ACTIVE)
            ->with('category')
            ->get();

        return view('professional-services.provider-profile', compact('profile', 'services'));
    }

    /**
     * Edit my provider profile
     */
    public function editProfile(): View
    {
        $user = Auth::user();
        $profile = ServiceProviderProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['is_available' => true]
        );

        return view('professional-services.edit-profile', compact('profile'));
    }

    /**
     * Update provider profile
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'is_available' => 'boolean',
            'hourly_rate' => 'nullable|numeric|min:0',
            'bio' => 'nullable|string|max:1000',
            'skills' => 'nullable|string',
            'portfolio_links' => 'nullable|string',
            'certifications' => 'nullable|string',
        ], [
            'skills.array' => 'Skills must be a valid list. Please provide skills as a comma-separated list or an array.',
            'portfolio_links.array' => 'Portfolio links must be a valid list of URLs.',
            'certifications.array' => 'Certifications must be a valid list.',
            'hourly_rate.numeric' => 'Hourly rate must be a valid number.',
            'hourly_rate.min' => 'Hourly rate cannot be negative.',
            'bio.max' => 'Bio cannot exceed 1000 characters.',
        ]);

        // Convert skills from JSON string to array
        if (isset($validated['skills']) && is_string($validated['skills'])) {
            $decoded = json_decode($validated['skills'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $validated['skills'] = array_filter($decoded);
            } else {
                // Fall back to comma-separated
                $validated['skills'] = array_filter(array_map('trim', explode(',', $validated['skills'])));
            }
        } else {
            $validated['skills'] = $validated['skills'] ?? [];
        }

        // Convert portfolio_links from JSON string to array
        if (isset($validated['portfolio_links']) && is_string($validated['portfolio_links'])) {
            $decoded = json_decode($validated['portfolio_links'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $validated['portfolio_links'] = array_filter($decoded);
            } else {
                $validated['portfolio_links'] = array_filter(array_map('trim', explode(',', $validated['portfolio_links'])));
            }
        } else {
            $validated['portfolio_links'] = $validated['portfolio_links'] ?? [];
        }

        // Convert certifications from JSON string to array
        if (isset($validated['certifications']) && is_string($validated['certifications'])) {
            $decoded = json_decode($validated['certifications'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $validated['certifications'] = array_filter($decoded);
            } else {
                $validated['certifications'] = array_filter(array_map('trim', explode(',', $validated['certifications'])));
            }
        } else {
            $validated['certifications'] = $validated['certifications'] ?? [];
        }

        $user = Auth::user();
        $result = $this->service->updateProviderProfile($user, $validated);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to update profile. Please check your input and try again.',
                'errors' => $result['errors'] ?? [],
            ], 400);
        }

        // Refresh the user model to get updated values after the profile update
        $user->refresh();

        if ($user->account_type === 'freelancer' && !$user->freelancer_profile_completed) {
            $user->update(['freelancer_profile_completed' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully. Your freelancer profile is now complete.',
        ]);
    }

    /**
     * Service provider directory
     */
    public function directory(Request $request): View
    {
        $query = ServiceProviderProfile::with('user')
            ->where('is_available', true);

        if ($request->skill) {
            $query->withSkill($request->skill);
        }

        if ($request->min_rating) {
            $query->where('average_rating', '>=', $request->min_rating);
        }

        $providers = $query->orderBy('average_rating', 'desc')
            ->paginate(20);

        // Get all unique skills for filter
        $allSkills = ServiceProviderProfile::whereNotNull('skills')
            ->pluck('skills')
            ->flatten()
            ->unique()
            ->sort()
            ->take(50);

        return view('professional-services.directory', compact('providers', 'allSkills'));
    }

    /**
     * Contact a service provider
     */
    public function contact(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'service_id' => 'nullable|exists:professional_services,id',
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
                    'action_url' => route('professional-services.provider-profile', $sender->id),
                ],
                'notify_chat_messages',
                true
            );

            $conversation = MarketplaceConversation::findOrCreate(
                'professional_service',
                (int) ($validated['service_id'] ?? 0),
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
