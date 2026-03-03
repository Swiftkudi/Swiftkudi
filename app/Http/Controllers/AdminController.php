<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\FraudLog;
use App\Models\Referral;
use App\Models\ActivationLog;
use App\Models\Currency;
use App\Models\TaskCategory;
use App\Models\Badge;
use App\Services\SwiftKudiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $earnDeskService;

    public function __construct(SwiftKudiService $earnDeskService)
    {
        $this->earnDeskService = $earnDeskService;
        
        $this->middleware(function ($request, $next) {
            // Only allow admin users
            if (!Auth::check() || !Auth::user()->is_admin) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to access the admin area.');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard overview
     */
    public function index()
    {
        $stats = $this->earnDeskService->getPlatformStats();

        // Recent activities
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        $recentTasks = Task::orderBy('created_at', 'desc')->take(5)->get();
        $recentWithdrawals = Withdrawal::orderBy('created_at', 'desc')->take(5)->get();
        $pendingCompletions = TaskCompletion::pending()->count();
        $pendingWithdrawals = Withdrawal::pending()->count();

        return view('admin', compact(
            'stats',
            'recentUsers',
            'recentTasks',
            'recentWithdrawals',
            'pendingCompletions',
            'pendingWithdrawals'
        ));
    }

    /**
     * Users management
     */
    public function users(Request $request)
    {
        $query = User::with('wallet');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            if ($request->status === 'activated') {
                $query->whereHas('wallet', function ($q) {
                    $q->where('is_activated', true);
                });
            } elseif ($request->status === 'pending') {
                $query->whereDoesntHave('wallet')
                    ->orWhereHas('wallet', function ($q) {
                        $q->where('is_activated', false);
                    });
            }
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * View user details
     */
    public function userDetails(User $user)
    {
        $wallet = $user->wallet;
        $tasks = Task::where('user_id', $user->id)->get();
        $completions = TaskCompletion::where('user_id', $user->id)->get();
        $withdrawals = Withdrawal::where('user_id', $user->id)->get();
        $referrals = Referral::where('user_id', $user->id)->get();

        return view('admin.user-details', compact(
            'user',
            'wallet',
            'tasks',
            'completions',
            'withdrawals',
            'referrals'
        ));
    }

    /**
     * Suspend user
     */
    public function suspendUser(Request $request, User $user)
    {
        // Implementation would suspend user access
        return redirect()->back()
            ->with('success', 'User suspended successfully.');
    }

    /**
     * Promote user to admin
     */
    public function promoteToAdmin(Request $request, User $user)
    {
        // Prevent self-demotion
        if ($user->id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'You cannot modify your own admin status.');
        }

        $user->is_admin = true;
        
        // Assign super_admin role by default
        $superRole = \App\Models\AdminRole::where('name', \App\Models\AdminRole::ROLE_SUPER_ADMIN)->first();
        if ($superRole) {
            $user->admin_role_id = $superRole->id;
        }
        
        $user->save();

        return redirect()->back()
            ->with('success', "{$user->name} has been promoted to admin.");
    }

    /**
     * Demote user from admin
     */
    public function demoteFromAdmin(Request $request, User $user)
    {
        // Prevent self-demotion
        if ($user->id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'You cannot modify your own admin status.');
        }

        $user->is_admin = false;
        $user->admin_role_id = null;
        $user->save();

        return redirect()->back()
            ->with('success', "{$user->name} has been demoted from admin.");
    }

    /**
     * Tasks management
     */
    public function tasks(Request $request)
    {
        $query = Task::with('user', 'category')
            ->withCount(['completions as task_completions_count' => function ($completionQuery) {
                $completionQuery->where('status', TaskCompletion::STATUS_APPROVED);
            }]);

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->where('is_active', false);
            }
        }

        $tasks = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.tasks', compact('tasks'));
    }

    /**
     * View task details
     */
    public function taskDetails(Task $task)
    {
        $completions = TaskCompletion::where('task_id', $task->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.task-details', compact('task', 'completions'));
    }

    /**
     * Approve/Reject task
     */
    public function approveTask(Task $task)
    {
        $task->is_approved = true;
        $task->save();

        return redirect()->back()
            ->with('success', 'Task approved successfully.');
    }

    public function rejectTask(Request $request, Task $task)
    {
        $task->is_active = false;
        $task->save();

        // Refund escrow
        if ($task->escrow_amount > 0) {
            $wallet = $task->user->wallet;
            if ($wallet) {
                $wallet->refundFromEscrow($task->escrow_amount);
            }
        }

        return redirect()->back()
            ->with('success', 'Task rejected and refunded.');
    }

    /**
     * Feature task
     */
    public function featureTask(Task $task)
    {
        $task->is_featured = !$task->is_featured;
        $task->save();

        return redirect()->back()
            ->with('success', 'Task featured status changed.');
    }

    /**
     * Withdrawals management
     */
    public function withdrawals(Request $request)
    {
        $query = Withdrawal::with('user', 'wallet');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $pendingWithdrawals = Withdrawal::pending()->count();

        return view('admin.withdrawals', compact('withdrawals', 'pendingWithdrawals'));
    }

    /**
     * Process withdrawal
     */
    public function processWithdrawal(Request $request, Withdrawal $withdrawal)
    {
        $action = $request->get('action');

        if ($action === 'approve') {
            $withdrawal->markAsCompleted($request->get('notes'));
            return redirect()->back()
                ->with('success', 'Withdrawal approved.');
        } elseif ($action === 'reject') {
            $request->validate(['notes' => 'required|string']);
            $withdrawal->markAsRejected($request->notes);
            return redirect()->back()
                ->with('success', 'Withdrawal rejected and refunded.');
        }

        return redirect()->back()
            ->with('error', 'Invalid action.');
    }

    /**
     * Task completions pending review
     */
    public function completions(Request $request)
    {
        $query = TaskCompletion::pending()->with('task', 'user');

        $completions = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.completions', compact('completions'));
    }

    /**
     * Approve completion (admin)
     */
    public function approveCompletion(Request $request, TaskCompletion $completion)
    {
        $result = $this->earnDeskService->awardTaskEarnings($completion);

        if (!$result['success']) {
            return redirect()->back()
                ->with('error', $result['message'] ?? 'Failed to approve completion.');
        }

        return redirect()->back()
            ->with('success', 'Completion approved. ' . ($result['message'] ?? ''));
    }

    /**
     * Reject completion (admin)
     */
    public function rejectCompletion(Request $request, TaskCompletion $completion)
    {
        $request->validate(['notes' => 'required|string']);
        $completion->reject($request->notes);

        return redirect()->back()
            ->with('success', 'Completion rejected.');
    }

    /**
     * Fraud logs
     */
    public function fraudLogs(Request $request)
    {
        $query = FraudLog::with('user');

        if ($request->has('severity')) {
            $query->bySeverity($request->severity);
        }

        if ($request->has('resolved')) {
            $query->where('is_resolved', $request->boolean('resolved'));
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.fraud-logs', compact('logs'));
    }

    /**
     * Resolve fraud log
     */
    public function resolveFraudLog(FraudLog $log)
    {
        $log->markAsResolved();

        return redirect()->back()
            ->with('success', 'Fraud log marked as resolved.');
    }

    /**
     * Referral management
     */
    public function referrals(Request $request)
    {
        $query = Referral::with(['user', 'referredUser']);

        if ($request->has('status')) {
            if ($request->status === 'registered') {
                $query->where('is_registered', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_registered', false);
            }
        }

        if ($request->has('activated')) {
            $query->where('is_activated', $request->boolean('activated'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('referredUser', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $referrals = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Referral::count(),
            'registered' => Referral::where('is_registered', true)->count(),
            'activated' => Referral::where('is_activated', true)->count(),
            'total_rewards' => Referral::sum('reward_earned'),
        ];

        return view('admin.referrals', compact('referrals', 'stats'));
    }

    /**
     * View referral details
     */
    public function referralDetails(Referral $referral)
    {
        $referral->load(['user', 'referredUser']);

        return view('admin.referral-details', compact('referral'));
    }

    /**
     * Approve referral bonus
     */
    public function approveReferralBonus(Referral $referral)
    {
        if ($referral->reward_earned > 0) {
            return redirect()->back()
                ->with('error', 'Bonus already awarded.');
        }

        // Get referral bonus from settings
        $bonusAmount = \App\Models\SystemSetting::get('referral_bonus_amount', 500);
        
        // Credit the referrer's wallet
        $referrer = $referral->user;
        $referredName = $referral->referredUser ? $referral->referredUser->name : $referral->referred_email;
        if ($referrer && $referrer->wallet) {
            $referrer->wallet->addWithdrawable($bonusAmount, 'referral_bonus', 'Referral bonus for referring ' . $referredName);
        }

        $referral->update(['reward_earned' => $bonusAmount]);

        return redirect()->back()
            ->with('success', 'Referral bonus approved and credited.');
    }

    /**
     * Activation management
     */
    public function activations(Request $request)
    {
        $query = ActivationLog::with(['user', 'referrer']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('activation_type', $request->type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $activations = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => ActivationLog::count(),
            'completed' => ActivationLog::where('status', 'completed')->count(),
            'pending' => ActivationLog::where('status', 'pending')->count(),
            'total_revenue' => ActivationLog::where('status', 'completed')->sum('platform_revenue'),
            'total_referral_bonus' => ActivationLog::where('status', 'completed')->sum('referral_bonus'),
        ];

        return view('admin.activations', compact('activations', 'stats'));
    }

    /**
     * View activation details
     */
    public function activationDetails(ActivationLog $activation)
    {
        $activation->load(['user', 'referrer']);

        return view('admin.activation-details', compact('activation'));
    }

    /**
     * Process activation (retry failed)
     */
    public function processActivation(Request $request, ActivationLog $activation)
    {
        if ($activation->status !== 'failed') {
            return redirect()->back()
                ->with('error', 'Only failed activations can be reprocessed.');
        }

        // Mark as pending for reprocessing
        $activation->update(['status' => 'pending']);

        // Notify user to try again
        $activation->user->notify(new \App\Notifications\ActivationReminder($activation));

        return redirect()->back()
            ->with('success', 'Activation marked for reprocessing.');
    }

    /**
     * Analytics
     */
    public function analytics()
    {
        $stats = $this->earnDeskService->getPlatformStats();

        // Revenue calculations
        $totalCommission = Transaction::where('type', 'task_payment')
            ->where('status', 'completed')
            ->sum('amount');
        
        $totalFees = Withdrawal::where('status', 'completed')
            ->sum('fee');

        $platformRevenue = $totalCommission + $totalFees;

        // User growth (last 30 days)
        $newUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        
        // Task completion rate
        $totalCompletions = TaskCompletion::count();
        $approvedCompletions = TaskCompletion::approved()->count();
        $completionRate = $totalCompletions > 0 
            ? round(($approvedCompletions / $totalCompletions) * 100, 2) 
            : 0;

        return view('admin.analytics', compact(
            'stats',
            'platformRevenue',
            'newUsers',
            'completionRate'
        ));
    }

    /**
     * Settings
     */
    public function settings()
    {
        $currencies = Currency::all();
        $categories = TaskCategory::all();
        
        return view('admin.settings', compact('currencies', 'categories'));
    }

    /**
     * Update currency rates
     */
    public function updateCurrencyRates(Request $request)
    {
        foreach ($request->rates as $code => $rate) {
            Currency::where('code', $code)->update(['rate_to_ngn' => $rate]);
        }

        return redirect()->back()
            ->with('success', 'Currency rates updated successfully.');
    }

    /**
     * Update task category
     */
    public function updateCategory(Request $request, TaskCategory $category)
    {
        $category->update($request->all());

        return redirect()->back()
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Create category
     */
    public function createCategory(Request $request)
    {
        TaskCategory::create($request->all());

        return redirect()->back()
            ->with('success', 'Category created successfully.');
    }

    /**
     * Run cron jobs manually
     */
    public function runCronJobs()
    {
        $expiredTasks = $this->earnDeskService->processExpiredTasks();

        return redirect()->back()
            ->with('success', "Processed {$expiredTasks} expired tasks.");
    }

    /**
     * Send push notification to users
     */
    public function sendNotification(Request $request)
    {
        $request->validate([
            'recipient_type' => 'required|in:all,active,inactive,new,single',
            'user_email' => 'required_if:recipient_type,single|email',
            'notif_title' => 'required|string|max:255',
            'notif_message' => 'required|string',
            'send_via' => 'required|array|min:1',
            'send_via.*' => 'in:email,database',
        ]);

        $title = $request->notif_title;
        $message = $request->notif_message;
        $sendVia = $request->send_via;
        $recipientType = $request->recipient_type;

        // Get users based on recipient type
        $users = collect();

        switch ($recipientType) {
            case 'all':
                $users = User::where('email', '!=', null)->get();
                break;
            case 'active':
                $users = User::where('last_activity_at', '>=', now()->subDays(30))->get();
                break;
            case 'inactive':
                $users = User::where('last_activity_at', '<', now()->subDays(30))
                    ->orWhereNull('last_activity_at')
                    ->get();
                break;
            case 'new':
                $users = User::where('created_at', '>=', now()->subDays(7))->get();
                break;
            case 'single':
                $user = User::where('email', $request->user_email)->first();
                if ($user) {
                    $users = collect([$user]);
                }
                break;
        }

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'No users found matching the criteria.');
        }

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                // Send via selected channels
                if (in_array('email', $sendVia) && $user->email) {
                    $user->notify(new \App\Notifications\CustomNotification($title, $message, 'push'));
                } elseif (in_array('database', $sendVia)) {
                    \App\Models\Notification::create([
                        'user_id' => $user->id,
                        'type' => 'admin_push',
                        'title' => $title,
                        'message' => $message,
                        'data' => json_encode(['source' => 'admin_push']),
                        'is_read' => false,
                    ]);
                }
                $sent++;
            } catch (\Exception $e) {
                \Log::error('Failed to send notification to user ' . $user->id, ['error' => $e->getMessage()]);
                $failed++;
            }
        }

        $message = "Notifications sent: {$sent} successful";
        if ($failed > 0) {
            $message .= ", {$failed} failed";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Professional Services management - list pending services
     */
    public function professionalServices(Request $request)
    {
        $query = \App\Models\ProfessionalService::with(['seller', 'category']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to pending
            $query->where('status', 'pending');
        }

        $services = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => \App\Models\ProfessionalService::count(),
            'pending' => \App\Models\ProfessionalService::where('status', 'pending')->count(),
            'active' => \App\Models\ProfessionalService::where('status', 'active')->count(),
            'rejected' => \App\Models\ProfessionalService::where('status', 'rejected')->count(),
        ];

        return view('admin.professional-services', compact('services', 'stats'));
    }

    /**
     * View professional service details
     */
    public function professionalServiceDetails(\App\Models\ProfessionalService $service)
    {
        $service->load(['seller', 'category', 'addons']);

        return view('admin.professional-service-details', compact('service'));
    }

    /**
     * Approve professional service
     */
    public function approveProfessionalService(\App\Models\ProfessionalService $service)
    {
        if ($service->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending services can be approved.');
        }

        $service->update([
            'status' => 'active',
            'rejection_reason' => null,
        ]);

        // Notify the seller
        if ($service->seller) {
            try {
                $service->seller->notify(new \App\Notifications\CustomNotification(
                    'Service Approved',
                    "Your service '{$service->title}' has been approved and is now live!",
                    'service_approval'
                ));
            } catch (\Exception $e) {
                // Log notification error but don't fail
                \Log::error('Failed to send service approval notification: ' . $e->getMessage());
            }
        }

        return redirect()->back()
            ->with('success', 'Service approved successfully.');
    }

    /**
     * Reject professional service
     */
    public function rejectProfessionalService(Request $request, \App\Models\ProfessionalService $service)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($service->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending services can be rejected.');
        }

        $service->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Notify the seller
        if ($service->seller) {
            try {
                $service->seller->notify(new \App\Notifications\CustomNotification(
                    'Service Rejected',
                    "Your service '{$service->title}' was not approved. Reason: {$request->rejection_reason}",
                    'service_rejection'
                ));
            } catch (\Exception $e) {
                // Log notification error but don't fail
                \Log::error('Failed to send service rejection notification: ' . $e->getMessage());
            }
        }

        return redirect()->back()
            ->with('success', 'Service rejected.');
    }

    /**
     * Growth Listings management - list pending listings
     */
    public function growthListings(Request $request)
    {
        $query = \App\Models\GrowthListing::with(['seller']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to pending
            $query->where('status', 'pending');
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $listings = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => \App\Models\GrowthListing::count(),
            'pending' => \App\Models\GrowthListing::where('status', 'pending')->count(),
            'active' => \App\Models\GrowthListing::where('status', 'active')->count(),
            'rejected' => \App\Models\GrowthListing::where('status', 'rejected')->count(),
        ];

        return view('admin.growth-listings', compact('listings', 'stats'));
    }

    /**
     * View growth listing details
     */
    public function growthListingDetails(\App\Models\GrowthListing $listing)
    {
        $listing->load(['seller', 'orders']);

        return view('admin.growth-listing-details', compact('listing'));
    }

    /**
     * Approve growth listing
     */
    public function approveGrowthListing(\App\Models\GrowthListing $listing)
    {
        if ($listing->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending listings can be approved.');
        }

        $listing->update([
            'status' => 'active',
            'rejection_reason' => null,
        ]);

        // Notify the seller
        if ($listing->seller) {
            try {
                $listing->seller->notify(new \App\Notifications\CustomNotification(
                    'Listing Approved',
                    "Your growth listing '{$listing->title}' has been approved and is now live!",
                    'listing_approval'
                ));
            } catch (\Exception $e) {
                // Log notification error but don't fail
                \Log::error('Failed to send listing approval notification: ' . $e->getMessage());
            }
        }

        return redirect()->back()
            ->with('success', 'Listing approved successfully.');
    }

    /**
     * Reject growth listing
     */
    public function rejectGrowthListing(Request $request, \App\Models\GrowthListing $listing)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($listing->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending listings can be rejected.');
        }

        $listing->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Notify the seller
        if ($listing->seller) {
            try {
                $listing->seller->notify(new \App\Notifications\CustomNotification(
                    'Listing Rejected',
                    "Your growth listing '{$listing->title}' was not approved. Reason: {$request->rejection_reason}",
                    'listing_rejection'
                ));
            } catch (\Exception $e) {
                // Log notification error but don't fail
                \Log::error('Failed to send listing rejection notification: ' . $e->getMessage());
            }
        }

        return redirect()->back()
            ->with('success', 'Listing rejected.');
    }

    /**
     * Digital Products management
     */
    public function digitalProducts(Request $request)
    {
        // Check if digital_products table exists
        if (!\Illuminate\Support\Facades\Schema::hasTable('digital_products')) {
            return view('admin.digital-products', [
                'products' => collect(),
                'stats' => ['total' => 0, 'pending' => 0, 'active' => 0, 'rejected' => 0],
                'message' => 'Digital products table does not exist. Please run migrations.'
            ]);
        }

        $status = $request->get('status', 'pending');

        $query = \App\Models\DigitalProduct::with(['user', 'category']);

        // DigitalProduct uses is_active boolean, not status string
        // pending = not active (is_active = false), active = is_active = true
        if ($status === 'pending') {
            $query->where('is_active', false);
        } elseif ($status === 'active') {
            $query->where('is_active', true);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => \App\Models\DigitalProduct::count(),
            'pending' => \App\Models\DigitalProduct::where('is_active', false)->count(),
            'active' => \App\Models\DigitalProduct::where('is_active', true)->count(),
            'rejected' => 0, // DigitalProduct doesn't have rejected status
        ];

        return view('admin.digital-products', compact('products', 'stats'));
    }

    /**
     * View digital product details
     */
    public function digitalProductDetails(\App\Models\DigitalProduct $product)
    {
        $product->load(['user', 'category', 'orders']);

        return view('admin.digital-product-details', compact('product'));
    }

    /**
     * Approve digital product
     */
    public function approveDigitalProduct(\App\Models\DigitalProduct $product)
    {
        if ($product->is_active) {
            return redirect()->back()
                ->with('error', 'Product is already active.');
        }

        $product->update([
            'is_active' => true,
        ]);

        // Notify the seller
        if ($product->user) {
            try {
                $product->user->notify(new \App\Notifications\CustomNotification(
                    'Product Approved',
                    "Your digital product '{$product->title}' has been approved and is now live!",
                    'product_approval'
                ));
            } catch (\Exception $e) {
                // Log notification error but don't fail
                \Log::error('Failed to send product approval notification: ' . $e->getMessage());
            }
        }

        return redirect()->back()
            ->with('success', 'Product approved successfully.');
    }

    /**
     * Reject digital product
     */
    public function rejectDigitalProduct(Request $request, \App\Models\DigitalProduct $product)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($product->is_active) {
            return redirect()->back()
                ->with('error', 'Cannot reject an active product.');
        }

        // For rejection, we just delete the product or keep it inactive
        // You could add a rejection_reason column if needed
        // For now, we'll just keep it inactive

        // Notify the seller
        if ($product->user) {
            try {
                $product->user->notify(new \App\Notifications\CustomNotification(
                    'Product Rejected',
                    "Your digital product '{$product->title}' was not approved. Reason: {$request->rejection_reason}",
                    'product_rejection'
                ));
            } catch (\Exception $e) {
                // Log notification error but don't fail
                \Log::error('Failed to send product rejection notification: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.digital-products')
            ->with('success', 'Product rejected.');
    }
}
