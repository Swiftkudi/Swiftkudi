<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\Task\CreateTaskController;
use App\Http\Controllers\Task\TaskController as NewTaskController;
use App\Http\Controllers\ProfessionalServiceController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\DigitalProductController;
use App\Http\Controllers\Admin\RevenueController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StartJourneyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\EscrowController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\BoostController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\IndexNowController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Health check endpoint (for monitoring)
Route::get('/health', [HealthController::class, 'check'])->name('health');

// Payment gateway webhooks (public, verified by signature)
Route::post('/webhooks/paystack', [PaymentController::class, 'paystackWebhook'])->name('webhooks.paystack');
Route::post('/webhooks/kora', [PaymentController::class, 'koraWebhook'])->name('webhooks.kora');
Route::post('/webhooks/stripe', [PaymentController::class, 'stripeWebhook'])->name('webhooks.stripe');

// Public routes
Route::get('/', function () {
    return view('landing');
})->name('home');
Route::view('/privacy-policy', 'legal.privacy')->name('legal.privacy');
Route::view('/terms-of-service', 'legal.terms')->name('legal.terms');

// SEO Sitemap Routes
Route::get('/sitemap.xml', [SitemapController::class, 'main'])->name('sitemap');
Route::get('/sitemap_index.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-tasks.xml', [SitemapController::class, 'tasks'])->name('sitemap.tasks');
Route::get('/sitemap-services.xml', [SitemapController::class, 'services'])->name('sitemap.services');

// IndexNow Route
Route::get('/indexnow-key.txt', [IndexNowController::class, 'getKey'])->name('indexnow.key');
// Short referral link - stores code in session then redirects to register
Route::get('/ref/{code}', [ReferralController::class, 'redirectWithCode'])->name('ref.redirect');

// Google OAuth Routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::post('/auth/google/one-tap', [GoogleAuthController::class, 'oneTap'])->name('auth.google.one-tap');

Route::middleware(['auth', 'verified', 'logout.inactive', 'onboarding'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User In-App Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\UserNotificationController::class, 'page'])->name('index');
        Route::get('/feed', [\App\Http\Controllers\UserNotificationController::class, 'index'])->name('feed');
        Route::post('/{id}/read', [\App\Http\Controllers\UserNotificationController::class, 'markRead'])->name('read');
        Route::post('/read-all', [\App\Http\Controllers\UserNotificationController::class, 'markAllRead'])->name('read-all');
    });
    
    // Push Notification Subscriptions
    Route::prefix('push')->name('push.')->group(function () {
        Route::post('/subscribe',   [PushSubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('unsubscribe');
        Route::post('/test',        [PushSubscriptionController::class, 'testPush'])->name('test');
    });

    // Start Your Journey - Mandatory Task Creation Gate Landing Page
    Route::get('/start-your-journey', [StartJourneyController::class, 'index'])->name('start-your-journey');
    Route::post('/start-your-journey/apply-bundle', [StartJourneyController::class, 'applyBundle'])->name('start-journey.apply-bundle');
    Route::get('/start-your-journey/check-status', [StartJourneyController::class, 'checkUnlockStatus'])->name('start-journey.check-status');
    Route::get('/start-your-journey/success', [StartJourneyController::class, 'unlockSuccess'])->name('start-journey.unlock-success');

    // Onboarding path for worker roles and buyers
    Route::get('/onboarding/select', [\App\Http\Controllers\OnboardingController::class, 'selectAccountType'])->name('onboarding.select');
    Route::post('/onboarding/select', [\App\Http\Controllers\OnboardingController::class, 'storeAccountType'])->name('onboarding.select.post');
    
    // API: Check account type status
    Route::get('/api/onboarding/account-type-status', [\App\Http\Controllers\OnboardingController::class, 'checkAccountTypeStatus'])->name('api.onboarding.account-type-status');
    Route::post('/onboarding/earn/activate', [\App\Http\Controllers\OnboardingController::class, 'activateEarner'])->name('onboarding.earn.activate');
    Route::post('/onboarding/earn/referral/complete', [\App\Http\Controllers\OnboardingController::class, 'completeReferralTask'])->name('onboarding.earn.referral.complete');
    Route::post('/onboarding/earn/referral/skip', [\App\Http\Controllers\OnboardingController::class, 'skipReferralTask'])->name('onboarding.earn.referral.skip');
    Route::post('/onboarding/earn/feature/unlock', [\App\Http\Controllers\OnboardingController::class, 'unlockEarnerFeature'])->name('onboarding.earn.feature.unlock');
    Route::get('/onboarding/feature/unlock/complete', [\App\Http\Controllers\OnboardingController::class, 'completePendingFeatureUnlock'])->name('onboarding.feature.unlock.complete');
    Route::post('/onboarding/freelancer/activate', [\App\Http\Controllers\OnboardingController::class, 'activateFreelancer'])->name('onboarding.freelancer.activate');
    Route::post('/onboarding/digital-product/activate', [\App\Http\Controllers\OnboardingController::class, 'activateDigitalProduct'])->name('onboarding.digital-product.activate');
    Route::post('/onboarding/growth/activate', [\App\Http\Controllers\OnboardingController::class, 'activateGrowth'])->name('onboarding.growth.activate');
    
    // Feature unlock routes for each role
    Route::post('/onboarding/buyer/feature/unlock', [\App\Http\Controllers\OnboardingController::class, 'unlockBuyerFeature'])->name('onboarding.buyer.feature.unlock');
    Route::post('/onboarding/task-creator/feature/unlock', [\App\Http\Controllers\OnboardingController::class, 'unlockTaskCreatorFeature'])->name('onboarding.task-creator.feature.unlock');
    Route::post('/onboarding/freelancer/feature/unlock', [\App\Http\Controllers\OnboardingController::class, 'unlockFreelancerFeature'])->name('onboarding.freelancer.feature.unlock');
    Route::post('/onboarding/digital-seller/feature/unlock', [\App\Http\Controllers\OnboardingController::class, 'unlockDigitalSellerFeature'])->name('onboarding.digital-seller.feature.unlock');
    Route::post('/onboarding/growth-seller/feature/unlock', [\App\Http\Controllers\OnboardingController::class, 'unlockGrowthSellerFeature'])->name('onboarding.growth-seller.feature.unlock');
    
    // Feature unlock page route
    Route::get('/onboarding/features', [\App\Http\Controllers\OnboardingController::class, 'showFeatureUnlock'])->name('onboarding.features');

    Route::get('/onboarding/task-creator', [\App\Http\Controllers\OnboardingController::class, 'taskCreatorOnboarding'])->name('onboarding.task-creator');
    Route::get('/onboarding/freelancer', [\App\Http\Controllers\OnboardingController::class, 'freelancerOnboarding'])->name('onboarding.freelancer');
    Route::get('/onboarding/digital-product', [\App\Http\Controllers\OnboardingController::class, 'digitalProductOnboarding'])->name('onboarding.digital-product');
    Route::get('/onboarding/growth', [\App\Http\Controllers\OnboardingController::class, 'growthOnboarding'])->name('onboarding.growth');
    Route::get('/onboarding/buyer', [\App\Http\Controllers\OnboardingController::class, 'buyerOnboarding'])->name('onboarding.buyer');
    Route::get('/onboarding/buyer/categories', [\App\Http\Controllers\OnboardingController::class, 'buyerCategorySelection'])->name('onboarding.buyer.categories');
    Route::post('/onboarding/buyer/categories', [\App\Http\Controllers\OnboardingController::class, 'storeBuyerCategories'])->name('onboarding.buyer.categories.store');
    
 // Buyer category preferences
        Route::get('/settings/buyer-categories', [\App\Http\Controllers\OnboardingController::class, 'buyerCategoriesForm'])->name('settings.buyer-categories');
        Route::post('/settings/buyer-categories', [\App\Http\Controllers\OnboardingController::class, 'updateBuyerCategories'])->name('settings.buyer-categories.update');


    // Dashboard sections
    Route::get('/dashboard/worker', [DashboardController::class, 'worker'])->name('dashboard.worker');
    Route::get('/dashboard/client', [DashboardController::class, 'client'])->name('dashboard.client');
    Route::get('/dashboard/leaderboard', [DashboardController::class, 'leaderboard'])->name('dashboard.leaderboard');
    Route::get('/dashboard/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
    Route::put('/dashboard/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::delete('/dashboard/profile', [DashboardController::class, 'deleteAccount'])->name('dashboard.profile.delete');

    // Referral routes
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::post('/referrals/register', [ReferralController::class, 'registerWithCode'])->name('referrals.register');
    Route::post('/referrals/check', [ReferralController::class, 'checkReferral'])->name('referrals.check');

    // Payment routes
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/initialize', [PaymentController::class, 'initialize'])->name('initialize');
        Route::get('/callback', [PaymentController::class, 'callback'])->name('callback');
    });

    // Wallet routes
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/escrow', [WalletController::class, 'escrow'])->name('escrow');
        Route::get('/activate', [WalletController::class, 'activate'])->name('activate');
        Route::post('/activate', [WalletController::class, 'processActivation'])->name('activate.process');
        Route::post('/activate/skip', [WalletController::class, 'skipActivation'])->name('activate.skip');
        Route::get('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/deposit', [WalletController::class, 'deposit'])->name('process-deposit');
        Route::get('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
        Route::post('/withdraw', [WalletController::class, 'processWithdrawal'])->name('process-withdrawal');
        Route::get('/balance', [WalletController::class, 'balance'])->name('balance');
        Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
        Route::post('/add-promo', [WalletController::class, 'addPromoCredit'])->name('add-promo');
    });

    // Task routes - Protected by task creation gate middleware
    Route::prefix('tasks')->name('tasks.')->middleware(['task.creation.gate', 'earner.access'])->group(function () {
        // New Create Task Module Routes
        Route::get('/create/new', [CreateTaskController::class, 'showCreateForm'])->name('create.new');
        Route::get('/create', function() { return redirect()->route('tasks.create.new'); })->name('create');
        Route::post('/create/store', [CreateTaskController::class, 'store'])->name('create.store');
        Route::post('/create/save-draft', [CreateTaskController::class, 'saveDraft'])->name('create.save-draft');
        Route::get('/create/get-draft', [CreateTaskController::class, 'getDraft'])->name('create.get-draft');
        Route::post('/create/clear-draft', [CreateTaskController::class, 'clearDraft'])->name('create.clear-draft');
        Route::post('/create/refresh-token', [CreateTaskController::class, 'refreshToken'])->name('create.refresh-token');
        Route::post('/create/validate', [CreateTaskController::class, 'validateTaskData'])->name('create.validate');
        Route::get('/create/calculate-cost', [CreateTaskController::class, 'calculateCost'])->name('create.calculate-cost');
        
        // Original task routes - redirect to new create flow
        Route::get('/', [TaskController::class, 'index'])->name('index');
        // /create route is defined above in new create module
        Route::get('/create/resume', [CreateTaskController::class, 'resume'])->name('create.resume');
        Route::get('/create/saved', [TaskController::class, 'savedCreate'])->name('create.saved');
        Route::post('/create/pay', [TaskController::class, 'payCreate'])->name('create.pay');
        Route::post('/tasks', [TaskController::class, 'store'])->name('store');
        Route::post('/tasks/suggest-bundles', [TaskController::class, 'suggestBundles'])->name('suggest-bundles');
        Route::get('/bundles', [TaskController::class, 'bundles'])->name('bundles');
        Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('my-tasks');
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::post('/{task}/submit', [TaskController::class, 'submit'])->name('submit');
        Route::post('/{task}/pause', [TaskController::class, 'pause'])->name('pause');
        Route::post('/{task}/resume', [TaskController::class, 'resume'])->name('resume');
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::get('/{task}/analytics', [TaskController::class, 'analytics'])->name('analytics');
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
        
        // Submission review routes
        Route::get('/submission/{completion}', [TaskController::class, 'submissionReview'])->name('submission.review');
        Route::post('/submission/{completion}/approve', [TaskController::class, 'approve'])->name('submission.approve');
        Route::post('/submission/{completion}/reject', [TaskController::class, 'reject'])->name('submission.reject');
        
        // Track platform click analytics
        Route::post('/track-platform-click', [TaskController::class, 'trackPlatformClick'])->name('track-platform-click');
    });

    // NEW TASK SYSTEM - Escrow-based Task Management
    Route::prefix('new-tasks')->name('new-tasks.')->middleware(['task.creation.gate', 'earner.access'])->group(function () {
        // Browse available tasks (worker view)
        Route::get('/', [NewTaskController::class, 'index'])->name('index');
        
        // Task creation (client view)
        Route::get('/create', [NewTaskController::class, 'create'])->name('create');
        Route::post('/store', [NewTaskController::class, 'store'])->name('store');
        
        // Task funding - moves funds from wallet to escrow
        Route::post('/{task}/fund', [NewTaskController::class, 'fundTask'])->name('fund');
        
        // My tasks (client view - tasks I created)
        Route::get('/my-tasks', [NewTaskController::class, 'myTasks'])->name('my-tasks');
        
        // Work tasks (worker view - available tasks to work on)
        Route::get('/work', [NewTaskController::class, 'workTasks'])->name('work');
        
        // Task details
        Route::get('/{task}', [NewTaskController::class, 'show'])->name('show');
        
        // Worker submission
        Route::post('/{task}/submit', [NewTaskController::class, 'submitWork'])->name('submit');
        
        // Submission management (client)
        Route::get('/{task}/submissions', [NewTaskController::class, 'submissions'])->name('submissions');
        Route::post('/{task}/submissions/{submission}/approve', [NewTaskController::class, 'approveSubmission'])->name('submissions.approve');
        Route::post('/{task}/submissions/{submission}/reject', [NewTaskController::class, 'rejectSubmission'])->name('submissions.reject');
        
        // Task actions
        Route::post('/{task}/pause', [NewTaskController::class, 'pauseTask'])->name('pause');
        Route::post('/{task}/resume', [NewTaskController::class, 'resumeTask'])->name('resume');
        Route::post('/{task}/cancel', [NewTaskController::class, 'cancelTask'])->name('cancel');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');

        // Settings routes - admin.settings index for layouts
        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings');

        Route::get('/settings/general', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.general')->defaults('group', 'general');
        Route::get('/settings/registration', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.registration')->defaults('group', 'registration');
        Route::get('/settings/commission', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.commission')->defaults('group', 'commission');
        Route::get('/settings/payment', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.payment')->defaults('group', 'payment');
        Route::get('/settings/smtp', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.smtp')->defaults('group', 'smtp');
        Route::get('/settings/currency', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.currency')->defaults('group', 'currency');
        Route::get('/settings/notifications', [\App\Http\Controllers\SettingsController::class, 'notificationMessages'])->name('settings.notifications');
        Route::get('/settings/notifications/audit', [\App\Http\Controllers\SettingsController::class, 'notificationAudit'])->name('settings.notifications-audit');
        Route::post('/settings/test-email', [\App\Http\Controllers\SettingsController::class, 'testEmail'])->name('settings.test-email');
        Route::post('/notifications/send', [\App\Http\Controllers\AdminController::class, 'sendNotification'])->name('notifications.send');
        Route::get('/settings/notification', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.notification')->defaults('group', 'notification');
        Route::get('/settings/security', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.security')->defaults('group', 'security');
        Route::get('/settings/cron', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.cron')->defaults('group', 'cron');
        Route::get('/settings/maintenance', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.maintenance')->defaults('group', 'maintenance');
        
        // Task Creation Gate settings
        Route::get('/settings/task-gate', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.task-gate')->defaults('group', 'task-gate');

        // Generic/grouped admin settings routes
        Route::get('/settings/{group}', [\App\Http\Controllers\SettingsController::class, 'group'])->name('settings.group');
        Route::put('/settings/{group}', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-smtp', [\App\Http\Controllers\SettingsController::class, 'testSmtp'])->name('settings.test-smtp');
        Route::post('/settings/test-gateway/{gateway}', [\App\Http\Controllers\SettingsController::class, 'testGateway'])->name('settings.test-gateway');
        Route::post('/settings/trigger-cron/{type}', [\App\Http\Controllers\SettingsController::class, 'triggerCron'])->name('settings.trigger-cron');
        Route::get('/settings/audit/logs', [\App\Http\Controllers\SettingsController::class, 'auditLogs'])->name('settings.audit');
        Route::post('/settings/initialize', [\App\Http\Controllers\SettingsController::class, 'initializeDefaults'])->name('settings.initialize');
        Route::post('/settings/clear-cache', [\App\Http\Controllers\SettingsController::class, 'clearCache'])->name('settings.clear-cache');


        // Analytics
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');

        // User management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}', [AdminController::class, 'userDetails'])->name('user-details');
        Route::post('/users/{user}/suspend', [AdminController::class, 'suspendUser'])->name('users.suspend');
        Route::post('/users/{user}/promote', [AdminController::class, 'promoteToAdmin'])->name('users.promote');
        Route::post('/users/{user}/demote', [AdminController::class, 'demoteFromAdmin'])->name('users.demote');
        Route::post('/users/{user}/clear-wallet', [AdminController::class, 'clearUserWallet'])->name('users.clear-wallet');
        Route::post('/users/{user}/remove-account-type', [AdminController::class, 'removeAccountType'])->name('users.remove-account-type');
        Route::post('/users/mass-remove-account-type', [AdminController::class, 'massRemoveAccountType'])->name('users.mass-remove-account-type');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');

        // Task management (admin)
        Route::get('/tasks', [AdminController::class, 'tasks'])->name('tasks');
        Route::get('/tasks/{task}', [AdminController::class, 'taskDetails'])->name('tasks.show');
        Route::post('/tasks/{task}/approve', [AdminController::class, 'approveTask'])->name('tasks.approve');
        Route::post('/tasks/{task}/reject', [AdminController::class, 'rejectTask'])->name('tasks.reject');
        Route::post('/tasks/{task}/feature', [AdminController::class, 'featureTask'])->name('tasks.feature');
        Route::delete('/tasks/{task}', [AdminController::class, 'deleteTask'])->name('tasks.delete');

        // Job management (admin)
        Route::get('/jobs', [AdminController::class, 'jobs'])->name('jobs');
        Route::get('/jobs/{job}', [AdminController::class, 'jobDetails'])->name('job-details');
        Route::delete('/jobs/{job}', [AdminController::class, 'deleteJob'])->name('jobs.delete');

        // Marketplace Management
        Route::get('/marketplace', [\App\Http\Controllers\Admin\MarketplaceController::class, 'index'])->name('marketplace.index');
        Route::get('/marketplace/create', [\App\Http\Controllers\Admin\MarketplaceController::class, 'create'])->name('marketplace.create');
        Route::post('/marketplace', [\App\Http\Controllers\Admin\MarketplaceController::class, 'store'])->name('marketplace.store');
        Route::get('/marketplace/{category}/edit', [\App\Http\Controllers\Admin\MarketplaceController::class, 'edit'])->name('marketplace.edit');
        Route::put('/marketplace/{category}', [\App\Http\Controllers\Admin\MarketplaceController::class, 'update'])->name('marketplace.update');
        Route::delete('/marketplace/{category}', [\App\Http\Controllers\Admin\MarketplaceController::class, 'destroy'])->name('marketplace.destroy');
        Route::post('/marketplace/{category}/toggle', [\App\Http\Controllers\Admin\MarketplaceController::class, 'toggle'])->name('marketplace.toggle');
        Route::post('/marketplace/bulk-action', [\App\Http\Controllers\Admin\MarketplaceController::class, 'bulkAction'])->name('marketplace.bulk-action');

        // Feature Toggles
        Route::get('/marketplace/features', [\App\Http\Controllers\Admin\MarketplaceController::class, 'features'])->name('marketplace.features');
        Route::post('/marketplace/features/toggle', [\App\Http\Controllers\Admin\MarketplaceController::class, 'toggleFeature'])->name('marketplace.toggle-feature');

        // Revenue reporting (admin)
        Route::get('/revenue', [\App\Http\Controllers\Admin\RevenueController::class, 'index'])->name('revenue.index');
        Route::get('/revenue/data', [\App\Http\Controllers\Admin\RevenueController::class, 'getRevenueData'])->name('revenue.data');
        Route::get('/revenue/expenses', [\App\Http\Controllers\Admin\RevenueController::class, 'expenses'])->name('revenue.expenses');
        Route::post('/revenue/expenses', [\App\Http\Controllers\Admin\RevenueController::class, 'createExpense'])->name('revenue.expenses.create');
        Route::get('/revenue/activations', [\App\Http\Controllers\Admin\RevenueController::class, 'activations'])->name('revenue.activations');
        Route::get('/revenue/export', [\App\Http\Controllers\Admin\RevenueController::class, 'export'])->name('revenue.export');
        Route::get('/revenue/stats', [\App\Http\Controllers\Admin\RevenueController::class, 'getQuickStats'])->name('revenue.stats');
        Route::match(['get','post'], '/revenue/refresh', [\App\Http\Controllers\Admin\RevenueController::class, 'refresh'])->name('revenue.refresh');
        Route::post('/revenue/clear-system-revenue', [\App\Http\Controllers\Admin\RevenueController::class, 'clearSystemRevenue'])->name('revenue.clear-system-revenue');
        Route::post('/revenue/clear-total-earned', [\App\Http\Controllers\Admin\RevenueController::class, 'clearTotalEarnings'])->name('revenue.clear-total-earned');
        Route::get('/revenue/chart-data', [\App\Http\Controllers\RevenueApiController::class, 'chartData'])->name('revenue.chart-data');
        Route::get('/revenue/drilldown', [\App\Http\Controllers\RevenueApiController::class, 'drilldown'])->name('revenue.drilldown');

        // Completion review
        Route::get('/completions', [AdminController::class, 'completions'])->name('completions');
        Route::post('/completions/{completion}/approve', [AdminController::class, 'approveCompletion'])->name('completions.approve');
        Route::post('/completions/{completion}/reject', [AdminController::class, 'rejectCompletion'])->name('completions.reject');

        // Withdrawal management
        Route::get('/withdrawals', [AdminController::class, 'withdrawals'])->name('withdrawals');
        Route::post('/withdrawals/{withdrawal}/process', [AdminController::class, 'processWithdrawal'])->name('withdrawals.process');

        // Convenience routes used by admin views for approve/reject actions
        Route::post('/withdrawals/{withdrawal}/approve', [AdminController::class, 'processWithdrawal'])->name('approve-withdrawal');
        Route::post('/withdrawals/{withdrawal}/reject', [AdminController::class, 'processWithdrawal'])->name('reject-withdrawal');

        // Fraud logs
        Route::get('/fraud-logs', [AdminController::class, 'fraudLogs'])->name('fraud-logs');
        Route::post('/fraud-logs/{log}/resolve', [AdminController::class, 'resolveFraudLog'])->name('fraud-logs.resolve');

        // Referral management
        Route::get('/referrals', [AdminController::class, 'referrals'])->name('referrals');
        Route::get('/referrals/{referral}', [AdminController::class, 'referralDetails'])->name('referrals.show');
        Route::post('/referrals/{referral}/approve', [AdminController::class, 'approveReferralBonus'])->name('referrals.approve');

        // Activation management
        Route::get('/activations', [AdminController::class, 'activations'])->name('activations');
        Route::get('/activations/{activation}', [AdminController::class, 'activationDetails'])->name('activations.show');
        Route::post('/activations/{activation}/process', [AdminController::class, 'processActivation'])->name('activations.process');

        // Professional Services management
        Route::get('/professional-services', [AdminController::class, 'professionalServices'])->name('professional-services');
        Route::get('/professional-services/{service}', [AdminController::class, 'professionalServiceDetails'])->name('professional-services.show');
        Route::post('/professional-services/{service}/approve', [AdminController::class, 'approveProfessionalService'])->name('professional-services.approve');
        Route::post('/professional-services/{service}/reject', [AdminController::class, 'rejectProfessionalService'])->name('professional-services.reject');
        Route::delete('/professional-services/{service}', [AdminController::class, 'deleteProfessionalService'])->name('professional-services.delete');

        // Growth Listings management
        Route::get('/growth-listings', [AdminController::class, 'growthListings'])->name('growth-listings');
        Route::get('/growth-listings/{listing}', [AdminController::class, 'growthListingDetails'])->name('growth-listings.show');
        Route::post('/growth-listings/{listing}/approve', [AdminController::class, 'approveGrowthListing'])->name('growth-listings.approve');
        Route::post('/growth-listings/{listing}/reject', [AdminController::class, 'rejectGrowthListing'])->name('growth-listings.reject');
        Route::delete('/growth-listings/{listing}', [AdminController::class, 'deleteGrowthListing'])->name('growth-listings.delete');

        // Digital Products management
        Route::get('/digital-products', [AdminController::class, 'digitalProducts'])->name('digital-products');
        Route::get('/digital-products/{product}', [AdminController::class, 'digitalProductDetails'])->name('digital-products.show');
        Route::post('/digital-products/{product}/approve', [AdminController::class, 'approveDigitalProduct'])->name('digital-products.approve');
        Route::post('/digital-products/{product}/reject', [AdminController::class, 'rejectDigitalProduct'])->name('digital-products.reject');
        Route::delete('/digital-products/{product}', [AdminController::class, 'deleteDigitalProduct'])->name('digital-products.delete');

        // Completions
        Route::delete('/completions/{completion}', [AdminController::class, 'deleteCompletion'])->name('completions.delete');

        // Fraud Logs
        Route::delete('/fraud-logs/{log}', [AdminController::class, 'deleteFraudLog'])->name('fraud-logs.delete');

        // Referrals
        Route::delete('/referrals/{referral}', [AdminController::class, 'deleteReferral'])->name('referrals.delete');

        // Withdrawals
        Route::delete('/withdrawals/{withdrawal}', [AdminController::class, 'deleteWithdrawal'])->name('withdrawals.delete');

        // Activations
        Route::delete('/activations/{activation}', [AdminController::class, 'deleteActivation'])->name('activations.delete');


        // Bulk delete
        Route::post('/users/bulk-delete',                [AdminController::class, 'bulkDeleteUsers'])->name('users.bulk-delete');
        Route::post('/users/bulk-clear-wallet',          [AdminController::class, 'bulkClearUserWallets'])->name('users.bulk-clear-wallet');
        Route::post('/users/bulk-reset-total-earned',    [AdminController::class, 'bulkResetUsersTotalEarned'])->name('users.bulk-reset-total-earned');
        Route::post('/tasks/bulk-delete',                [AdminController::class, 'bulkDeleteTasks'])->name('tasks.bulk-delete');
        Route::post('/professional-services/bulk-delete',[AdminController::class, 'bulkDeleteProfessionalServices'])->name('professional-services.bulk-delete');
        Route::post('/growth-listings/bulk-delete',      [AdminController::class, 'bulkDeleteGrowthListings'])->name('growth-listings.bulk-delete');
        Route::post('/digital-products/bulk-delete',     [AdminController::class, 'bulkDeleteDigitalProducts'])->name('digital-products.bulk-delete');
        Route::post('/completions/bulk-delete',          [AdminController::class, 'bulkDeleteCompletions'])->name('completions.bulk-delete');
        Route::post('/fraud-logs/bulk-delete',           [AdminController::class, 'bulkDeleteFraudLogs'])->name('fraud-logs.bulk-delete');
        Route::post('/referrals/bulk-delete',            [AdminController::class, 'bulkDeleteReferrals'])->name('referrals.bulk-delete');
        Route::post('/withdrawals/bulk-delete',          [AdminController::class, 'bulkDeleteWithdrawals'])->name('withdrawals.bulk-delete');
        Route::post('/activations/bulk-delete',          [AdminController::class, 'bulkDeleteActivations'])->name('activations.bulk-delete');

         // end admin routes
    });

    // Cron jobs
    Route::post('/cron', [AdminController::class, 'runCronJobs'])->name('cron');

    // Chat/Messaging routes
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::get('/open/{type}/{referenceId}/{participantId}', [ChatController::class, 'open'])->name('open');
        Route::get('/{conversation}', [ChatController::class, 'show'])->name('show');
        Route::post('/message', [ChatController::class, 'store'])->name('message');
        Route::get('/{conversation}/messages', [ChatController::class, 'apiMessages'])->name('messages');
        Route::post('/send', [ChatController::class, 'apiSend'])->name('send');
        Route::post('/start', [ChatController::class, 'startConversation'])->name('start');
        Route::get('/unread', [ChatController::class, 'getUnreadCount'])->name('unread');
        Route::post('/{conversation}/read', [ChatController::class, 'markAsRead'])->name('read');
        Route::post('/{conversation}/close', [ChatController::class, 'closeConversation'])->name('close');
    });
});

// Professional Services (Hire) - Available to all authenticated users, but restricted for earners until unlocked
Route::prefix('services')->name('professional-services.')->middleware(['earner.access'])->group(function () {
    // Public - Browse services
    Route::get('/', [ProfessionalServiceController::class, 'index'])->name('index');
    Route::get('/search', [ProfessionalServiceController::class, 'index'])->name('search');
    
    // Service provider directory
    Route::get('/directory', [ProfessionalServiceController::class, 'directory'])->name('directory');
    Route::get('/provider/{userId}', [ProfessionalServiceController::class, 'providerProfile'])->name('provider-profile');
    
    // Protected routes - require authentication
    Route::middleware(['auth', 'verified'])->group(function () {
        // Create service
        Route::get('/create', [ProfessionalServiceController::class, 'create'])->name('create');
        Route::post('/store', [ProfessionalServiceController::class, 'store'])->name('store');
        
        // My services
        Route::get('/my-services', [ProfessionalServiceController::class, 'myServices'])->name('my-services');
        
        // Order
        Route::post('/{service}/order', [ProfessionalServiceController::class, 'createOrder'])->name('order');
        Route::get('/checkout/resume', [ProfessionalServiceController::class, 'resumeCheckout'])->name('checkout.resume');
        
        // My orders (buyer)
        Route::get('/orders', [ProfessionalServiceController::class, 'myOrders'])->name('orders.index');
        Route::get('/orders/{order}', [ProfessionalServiceController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders/{order}/approve', [ProfessionalServiceController::class, 'approveOrder'])->name('orders.approve');
        Route::post('/orders/{order}/revision', [ProfessionalServiceController::class, 'requestRevision'])->name('orders.revision');
        Route::post('/orders/{order}/cancel', [ProfessionalServiceController::class, 'cancelOrder'])->name('orders.cancel');
        Route::post('/orders/{order}/message', [ProfessionalServiceController::class, 'sendMessage'])->name('orders.message');
        Route::post('/orders/{order}/review', [ProfessionalServiceController::class, 'leaveReview'])->name('orders.review');
        
        // My sales (seller)
        Route::get('/sales', [ProfessionalServiceController::class, 'mySales'])->name('sales.index');
        Route::post('/orders/{order}/deliver', [ProfessionalServiceController::class, 'deliverOrder'])->name('orders.deliver');
        
        // Provider profile
        Route::get('/profile/edit', [ProfessionalServiceController::class, 'editProfile'])->name('edit-profile');
        Route::put('/profile', [ProfessionalServiceController::class, 'updateProfile'])->name('update-profile');
        
        // Contact seller
        Route::post('/contact', [ProfessionalServiceController::class, 'contact'])->name('contact');
    });
    
    // Single service view (public)
    Route::get('/{service}', [ProfessionalServiceController::class, 'show'])->name('show');
});

// Growth Marketplace - Available to all authenticated users, but restricted for earners until unlocked
Route::prefix('growth')->name('growth.')->middleware(['earner.access'])->group(function () {
    // Public - Browse
    Route::get('/', [GrowthController::class, 'index'])->name('index');
    
    // Protected routes - require authentication
    Route::middleware(['auth', 'verified'])->group(function () {
        // Create listing - MUST be before /{type} route
        Route::get('/create', [GrowthController::class, 'create'])->name('create');
        Route::post('/store', [GrowthController::class, 'store'])->name('store');
        
        // My listings
        Route::get('/my-listings', [GrowthController::class, 'myListings'])->name('my-listings');
        
        // Order
        Route::post('/{listing}/order', [GrowthController::class, 'createOrder'])->name('order');
        Route::get('/checkout/resume', [GrowthController::class, 'resumeCheckout'])->name('checkout.resume');
        
        // Orders (buyer)
        Route::get('/orders', [GrowthController::class, 'myOrders'])->name('orders.index');
        Route::get('/orders/{order}', [GrowthController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders/{order}/approve', [GrowthController::class, 'approveOrder'])->name('orders.approve');
        Route::post('/orders/{order}/revision', [GrowthController::class, 'requestRevision'])->name('orders.revision');
        Route::post('/orders/{order}/cancel', [GrowthController::class, 'cancelOrder'])->name('orders.cancel');
        
        // Sales (seller)
        Route::get('/sales', [GrowthController::class, 'mySales'])->name('sales.index');
        Route::post('/orders/{order}/proof', [GrowthController::class, 'submitProof'])->name('orders.proof');
        
        // Contact seller
        Route::post('/contact', [GrowthController::class, 'contact'])->name('contact');
    });
    
    // Type filter - MUST be after /create route
    Route::get('/{type}', [GrowthController::class, 'index'])->name('type');
    
    // Single listing view (public)
    Route::get('/listing/{listing}', [GrowthController::class, 'show'])->name('show');
});

// Digital Products Marketplace - Restricted for earners until unlocked
Route::prefix('products')->name('digital-products.')->middleware(['earner.access'])->group(function () {
    // Public - Browse
    Route::get('/', [DigitalProductController::class, 'index'])->name('index');
    Route::get('/featured', [DigitalProductController::class, 'featured'])->name('featured');
    
    // Protected routes - require authentication
    Route::middleware(['auth', 'verified'])->group(function () {
        // Create product
        Route::get('/create', [DigitalProductController::class, 'create'])->name('create');
        Route::post('/store', [DigitalProductController::class, 'store'])->name('store');
        
        // My products
        Route::get('/my-products', [DigitalProductController::class, 'myProducts'])->name('my-products');
        Route::get('/my-products/{product}/edit', [DigitalProductController::class, 'edit'])->name('edit');
        Route::put('/my-products/{product}', [DigitalProductController::class, 'update'])->name('update');
        Route::delete('/my-products/{product}', [DigitalProductController::class, 'destroy'])->name('destroy');
        
        // Purchase
        Route::post('/{product}/purchase', [DigitalProductController::class, 'purchase'])->name('purchase');
        Route::get('/purchase/resume', [DigitalProductController::class, 'resumePurchase'])->name('purchase.resume');
        
        // My purchases
        Route::get('/purchases', [DigitalProductController::class, 'myPurchases'])->name('my-purchases');
        Route::get('/orders/{order}/download', [DigitalProductController::class, 'download'])->name('download');
        Route::post('/orders/{order}/confirm', [DigitalProductController::class, 'confirmReceipt'])->name('confirm-receipt');
        
        // Reviews
        Route::post('/{product}/review', [DigitalProductController::class, 'review'])->name('review');
    });
    
    // Single product view (public)
    Route::get('/{product}', [DigitalProductController::class, 'show'])->name('show');
});

// Job Board
Route::prefix('jobs')->name('jobs.')->group(function () {
    // Public - Browse
    Route::get('/', [JobController::class, 'index'])->name('index');
    
    // Protected routes - require authentication
    Route::middleware(['auth', 'verified'])->group(function () {
        // Create job
        Route::get('/create', [JobController::class, 'create'])->name('create');
        Route::post('/store', [JobController::class, 'store'])->name('store');
        
        // My jobs
        Route::get('/my-jobs', [JobController::class, 'myJobs'])->name('my-jobs');
        Route::get('/{job}/edit', [JobController::class, 'edit'])->name('edit');
        Route::put('/{job}', [JobController::class, 'update'])->name('update');
        Route::delete('/{job}', [JobController::class, 'destroy'])->name('destroy');
        Route::post('/{job}/close', [JobController::class, 'close'])->name('close');
        
        // Applications
        Route::get('/applications', [JobController::class, 'applications'])->name('applications');
        Route::post('/{job}/apply', [JobController::class, 'apply'])->name('apply');
        Route::post('/applications/{application}/withdraw', [JobController::class, 'withdrawApplication'])->name('withdraw');
        Route::post('/applications/{application}/hire', [JobController::class, 'hireApplicant'])->name('hire');
        Route::post('/applications/{application}/reject', [JobController::class, 'rejectApplicant'])->name('reject');
    });
    
    // Single job view (public)
    Route::get('/{job}', [JobController::class, 'show'])->name('show');
});

// Escrow Management
Route::prefix('escrow')->name('escrow.')->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/', [EscrowController::class, 'index'])->name('index');
        Route::get('/active', [EscrowController::class, 'active'])->name('active');
        Route::get('/released', [EscrowController::class, 'released'])->name('released');
        Route::get('/disputed', [EscrowController::class, 'disputed'])->name('disputed');
        Route::get('/{escrow}', [EscrowController::class, 'show'])->name('show');
        Route::post('/{escrow}/release', [EscrowController::class, 'release'])->name('release');
        Route::post('/{escrow}/cancel', [EscrowController::class, 'cancel'])->name('cancel');
    });
});

// Disputes
Route::prefix('disputes')->name('disputes.')->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/', [DisputeController::class, 'index'])->name('index');
        Route::get('/create', [DisputeController::class, 'create'])->name('create');
        Route::post('/store', [DisputeController::class, 'store'])->name('store');
        Route::get('/{dispute}', [DisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/respond', [DisputeController::class, 'respond'])->name('respond');
        Route::post('/{dispute}/evidence', [DisputeController::class, 'submitEvidence'])->name('evidence');
    });
});

// Verification Center
Route::prefix('verification')->name('verification.')->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/', [VerificationController::class, 'index'])->name('index');
        Route::post('/email', [VerificationController::class, 'verifyEmail'])->name('email');
        Route::post('/phone', [VerificationController::class, 'verifyPhone'])->name('phone');
        Route::post('/phone/verify-code', [VerificationController::class, 'verifyPhoneCode'])->name('phone.verify-code');
        Route::post('/identity', [VerificationController::class, 'verifyIdentity'])->name('identity');
        Route::post('/address', [VerificationController::class, 'verifyAddress'])->name('address');
    });
});

// Boost & Promotion
Route::prefix('boost')->name('boost.')->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/', [BoostController::class, 'index'])->name('index');
        Route::get('/items', [BoostController::class, 'getItems'])->name('items');
        Route::post('/activate', [BoostController::class, 'activate'])->name('activate');
        Route::post('/extend/{boost}', [BoostController::class, 'extend'])->name('extend');
        Route::delete('/cancel/{boost}', [BoostController::class, 'cancel'])->name('cancel');
    });
});

// CSRF token endpoint for AJAX keep-alives
Route::get('/_csrf-token', function(){
    return response()->json([
        'token' => csrf_token(),
        'session_lifetime' => config('session.lifetime')
    ]);
});

require __DIR__.'/auth.php';


// SEO Pages
Route::view('/about', 'pages.about')->name('pages.about');
Route::view('/learn', 'learn.index')->name('learn.index');
Route::view('/about-author', 'pages.about-author')->name('pages.about-author');
Route::view('/editorial-policy', 'pages.editorial-policy')->name('pages.editorial-policy');
Route::view('/how-payments-work', 'pages.how-payments-work')->name('pages.how-payments-work');
Route::view('/platform-safety', 'pages.platform-safety')->name('pages.platform-safety');
Route::view('/press', 'pages.press')->name('pages.press');

