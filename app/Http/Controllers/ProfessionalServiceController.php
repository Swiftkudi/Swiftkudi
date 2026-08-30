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
use App\Services\NotificationManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfessionalServiceController extends Controller
{
    protected $service;
    protected NotificationManager $notificationManager;

    public function __construct(ProfessionalServiceService $service, NotificationManager $notificationManager)
    {
        $this->service = $service;
        $this->notificationManager = $notificationManager;
    }

    /**
     * Browse services (public)
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:190'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'delivery_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'sort' => ['nullable', 'in:recommended,newest,price_asc,price_desc'],
            'per_page' => ['nullable', 'integer', 'in:10,15,25,50'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 15);
        $query = ProfessionalService::active()->with(['category', 'seller']);

        // Preserve the existing buyer onboarding/category preferences without
        // hiding the rest of the marketplace from users who have not opted in.
        $user = auth()->user();
        if ($user && $user->account_type === 'buyer' &&
            \App\Services\OnboardingSettingsService::isBuyerOnboardingEnabled() &&
            \App\Services\OnboardingSettingsService::isBuyerCategorySelectionRequired() &&
            $user->buyer_onboarding_completed) {
            $buyerCategories = $user->getBuyerCategories();
            if (!empty($buyerCategories)) {
                $query->whereIn('category_id', $buyerCategories);
            }
        }

        if (!empty($validated['category'])) {
            $query->ofCategory($validated['category']);
        }

        if (!empty($validated['search'])) {
            $query->search(trim($validated['search']));
        }

        if (isset($validated['min_price'])) {
            $query->where('price', '>=', (float) $validated['min_price']);
        }
        if (isset($validated['max_price'])) {
            $query->where('price', '<=', (float) $validated['max_price']);
        }
        if (isset($validated['delivery_days'])) {
            $query->where('delivery_days', '<=', (int) $validated['delivery_days']);
        }

        switch ($validated['sort'] ?? 'recommended') {
            case 'newest':
                $query->latest();
                break;
            case 'price_asc':
                $query->orderBy('price')->latest('id');
                break;
            case 'price_desc':
                $query->orderByDesc('price')->latest('id');
                break;
            default:
                $query->orderByDesc('is_featured')->latest();
                break;
        }

        $services = $query->paginate($perPage)->withQueryString();
        $categories = ProfessionalServiceCategory::active()->orderBy('name')->get();

        return view('professional-services.index', compact('services', 'categories'));
    }

    /**
     * Show service details
     */
    public function show(ProfessionalService $service): View
    {
        $service->load(['category', 'seller', 'addons']);

        $userHasOrder = false;
        if (Auth::check()) {
            $userHasOrder = ProfessionalServiceOrder::where('service_id', $service->id)
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

        $this->notificationManager->notify(
            NotificationManager::EVENT_SERVICE_CREATED,
            $user,
            [
                'service_id' => $result['service']->id ?? null,
                'service_title' => $result['service']->title ?? $validated['title'],
                'action_url' => route('professional-services.my-services'),
            ]
        );

        if ($user->account_type === 'freelancer' && !$user->freelancer_service_created) {
            // Use centralized service for unlock logic
            app(\App\Services\TaskGateProgressService::class)->unlockMarketplaceSeller(
                $user,
                'freelancer'
            );
        }

        // Check for next onboarding step after service creation
        $nextStep = null;
        if ($user->account_type === 'freelancer' && $user->freelancer_service_created) {
            $user->refresh(); // Refresh to get updated fields
            $nextStep = app(\App\Services\AccessGateService::class)->getNextOnboardingStep($user);
        }

        $response = [
            'success' => true,
            'message' => $result['message'],
            'redirect' => route('professional-services.my-services'),
        ];

        if ($nextStep) {
            $response['next_step_redirect'] = route($nextStep['route']);
            $response['next_step_message'] = $nextStep['message'];
        }

        return response()->json($response);
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
    public function createOrder(Request $request, ProfessionalService $service)
    {
        $validated = $request->validate([
            'addon_ids' => 'nullable|array',
            'addon_ids.*' => 'integer',
            'requirements' => 'required|string|min:10',
        ]);

        $user = Auth::user();
        $result = $this->service->createOrder(
            $user, 
            $service->id, 
            $validated['addon_ids'] ?? [],
            $validated['requirements']
        );

        if (!$result['success']) {
            if (isset($result['required'], $result['available'])) {
                $requiredTopup = max(0, (float) $result['required'] - (float) $result['available']);
                session([
                    'pending_service_checkout' => [
                        'service_id' => $service->id,
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

            $this->notificationManager->notify(
                NotificationManager::EVENT_SERVICE_ORDER_SELLER,
                $result['order']->seller,
                [
                    'order_id' => $result['order']->id,
                    'service_title' => $result['order']->service->title ?? 'Professional Service',
                    'action_url' => route('professional-services.orders.show', $result['order']->id),
                ]
            );

            $this->notificationManager->notify(
                NotificationManager::EVENT_SERVICE_ORDER_BUYER,
                $result['order']->buyer,
                [
                    'order_id' => $result['order']->id,
                    'service_title' => $result['order']->service->title ?? 'Professional Service',
                    'action_url' => route('professional-services.orders.show', $result['order']->id),
                ]
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

            $this->notificationManager->notify(
                NotificationManager::EVENT_SERVICE_ORDER_SELLER,
                $result['order']->seller,
                [
                    'order_id' => $result['order']->id,
                    'service_title' => $result['order']->service->title ?? 'Professional Service',
                    'action_url' => route('professional-services.orders.show', $result['order']->id),
                ]
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
    public function providerProfile(int $userId)
    {
        $profile = ServiceProviderProfile::with('user')->where('user_id', $userId)->firstOrFail();
        $this->ensureProfileSlug($profile);

        return redirect()->route('freelancers.show', $profile->slug, 301);
    }

    /**
     * Public, SEO-friendly freelancer profile.
     */
    public function freelancerProfile(string $slug): View
    {
        $profile = ServiceProviderProfile::with('user')->where('slug', $slug)->firstOrFail();
        if (!Auth::check() || Auth::id() !== $profile->user_id) {
            $profile->increment('profile_views');
        }

        $services = ProfessionalService::where('user_id', $profile->user_id)
            ->where('status', ProfessionalService::STATUS_ACTIVE)
            ->with('category')
            ->latest()
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
        $this->ensureProfileSlug($profile);

        return view('professional-services.edit-profile', compact('profile'));
    }

    /**
     * Update provider profile
     */

 public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'professional_title' => 'required|string|min:3|max:160',
            'is_available' => 'boolean',
            'availability_note' => 'nullable|string|max:255',
            'hourly_rate' => 'nullable|numeric|min:0|max:100000000',
            'bio' => 'required|string|min:40|max:3000',
            'skills' => 'nullable|string',
            'languages' => 'nullable|string',
            'education' => 'nullable|string',
            'work_experience' => 'nullable|string',
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

        foreach (['languages', 'education', 'work_experience'] as $field) {
            if (isset($validated[$field]) && is_string($validated[$field])) {
                $decoded = json_decode($validated[$field], true);
                $validated[$field] = json_last_error() === JSON_ERROR_NONE && is_array($decoded)
                    ? array_values(array_filter($decoded))
                    : array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated[$field]))));
            } else {
                $validated[$field] = $validated[$field] ?? [];
            }
        }

        $user = Auth::user();
        $result = $this->service->updateProviderProfile($user, $validated);
        if (($result['success'] ?? false) && isset($result['profile'])) {
            $this->ensureProfileSlug($result['profile']);
        }

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

        // Check for next onboarding step after profile completion
        $nextStep = null;
        if ($user->account_type === 'freelancer' && $user->freelancer_profile_completed) {
            $user->refresh(); // Refresh to get updated fields
            $nextStep = app(\App\Services\AccessGateService::class)->getNextOnboardingStep($user);
        }

        $response = [
            'success' => true,
            'message' => $nextStep
                ? 'Profile updated successfully. Please complete the next step.'
                : 'Profile updated successfully. Your freelancer profile is now complete.',
      ];

        if ($nextStep) {
            $response['next_step_redirect'] = route($nextStep['route']);
            $response['next_step_message'] = $nextStep['message'];
        }

        return response()->json($response);
    }




    /**
     * Service provider directory
     */
    public function directory(Request $request): View
    {
        $validated = $request->validate([
            'skill' => 'nullable|string|max:100',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'search' => 'nullable|string|max:120',
            'max_rate' => 'nullable|numeric|min:0|max:1000000000',
        ]);

        $query = ServiceProviderProfile::with('user')
            ->where('is_available', true);

        if (!empty($validated['skill'])) {
            $query->withSkill($validated['skill']);
        }

        if (isset($validated['min_rating'])) {
            $query->where('average_rating', '>=', $validated['min_rating']);
        }

        if (!empty($validated['search'])) {
            $term = trim($validated['search']);
            $query->where(function ($q) use ($term) {
                $q->where('professional_title', 'like', "%{$term}%")
                    ->orWhere('bio', 'like', "%{$term}%")
                    ->orWhere('skills', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%"));
            });
        }

        if (isset($validated['max_rate'])) {
            $query->where('hourly_rate', '<=', (float) $validated['max_rate']);
        }

        $providers = $query->orderBy('average_rating', 'desc')
            ->orderByDesc('total_orders_completed')
            ->paginate(20)
            ->withQueryString();

        // Get all unique skills for filter
        $allSkills = ServiceProviderProfile::whereNotNull('skills')
            ->pluck('skills')
            ->flatMap(function ($skills) {
                if (is_array($skills)) {
                    return $skills;
                }

                if (is_string($skills)) {
                    $decoded = json_decode($skills, true);
                    return is_array($decoded) ? $decoded : [$skills];
                }

                return [];
            })
            ->filter(fn ($skill) => is_string($skill) && trim($skill) !== '')
            ->map(fn ($skill) => trim($skill))
            ->unique(fn ($skill) => mb_strtolower($skill))
            ->sort()
            ->values()
            ->take(50);

        return view('professional-services.directory', compact('providers', 'allSkills'));
    }

    private function ensureProfileSlug(ServiceProviderProfile $profile): void
    {
        if ($profile->slug) return;

        $name = optional($profile->user)->name ?: optional($profile->user()->first())->name ?: 'freelancer';
        $base = Str::slug($name) ?: 'freelancer';
        $profile->slug = $base . '-' . $profile->user_id;
        $profile->save();
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

            $this->notificationManager->notify(
                NotificationManager::EVENT_SERVICE_MESSAGE_RECEIVED,
                $recipient,
                [
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->name,
                    'subject' => $validated['subject'],
                    'message' => $validated['message'],
                    'action_url' => route('professional-services.provider-profile', $sender->id),
                ]
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
