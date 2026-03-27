<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\Task;
use App\Models\Wallet;
use App\Services\AccountTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    /**
     * Account type service instance.
     */
    protected AccountTypeService $accountTypeService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->accountTypeService = app(AccountTypeService::class);
    }

    /**
     * Show the account type selection page.
     */
    public function selectAccountType()
    {
        $user = Auth::user();

        // Ensure user is authenticated
        if (!$user) {
            return redirect()->route('login');
        }

        // If user already has an account type AND onboarding is completed, redirect to dashboard
        if ($user->account_type && $user->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        // If user has an account type but onboarding is not completed, 
        // mark onboarding as completed and redirect to dashboard
        if ($user->account_type && !$user->onboarding_completed) {
            $user->onboarding_completed = true;
            $user->save();
            
            return redirect()
                ->route('dashboard')
                ->with('success', 'Welcome back! Your account is now active.');
        }

        // Check if user already has an account type (without sending notification)
        $accountCheck = $this->accountTypeService->checkAndRedirect($user, false);
        
        if ($accountCheck['has_account_type']) {
            // User already has an account type - redirect to dashboard with info
            return redirect()
                ->route('dashboard')
                ->with('info', $accountCheck['message']);
        }

        if ($user->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.select-type');
    }

    /**
     * API: Check user's account type status.
     * Returns JSON response with account type information.
     */
    public function checkAccountTypeStatus(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $hasAccountType = $this->accountTypeService->hasAccountType($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'has_account_type' => $hasAccountType,
                'account_type' => $this->accountTypeService->getAccountType($user),
                'account_type_label' => $this->accountTypeService->getAccountTypeLabel($user->account_type),
                'onboarding_completed' => (bool) $user->onboarding_completed,
                'can_change_type' => $this->accountTypeService->canChangeAccountType($user),
                'redirect_url' => $hasAccountType 
                    ? route('dashboard')
                    : null,
            ],
        ]);
    }

    public function storeAccountType(Request $request)
    {
        $request->validate([
            'account_type' => 'required|in:earner,task_creator,freelancer,digital_seller,growth_seller,buyer',
        ]);

        $user = Auth::user();

        $updateData = [
            'account_type' => $request->account_type,
            'onboarding_started_at' => now(),
        ];

        if (in_array($request->account_type, ['earner', 'task_creator', 'freelancer'])) {
            // Onboarding flow selected; keep user moving into primary journey path.
            $updateData['onboarding_completed'] = true;
            $updateData['onboarding_completed_at'] = now();
        }

        if ($request->account_type === 'buyer') {
            // Buyers need to select categories before completing onboarding
            $updateData['onboarding_completed'] = false;
            $updateData['buyer_onboarding_completed'] = false;
        }

        if ($request->account_type === 'task_creator') {
            // Task creators must create first task before full marketplace access.
            $updateData['has_completed_mandatory_creation'] = false;
        }

        if ($request->account_type === 'earner') {
            // Earner will follow the start journey gate, no direct activation gateway.
            $updateData['has_completed_mandatory_creation'] = false;
        }

        if ($request->account_type === 'freelancer') {
            $updateData['has_completed_mandatory_creation'] = false;
            $updateData['freelancer_activation_paid'] = false;
            $updateData['freelancer_profile_completed'] = false;
            $updateData['freelancer_service_created'] = false;
        }

        if ($request->account_type === 'digital_seller') {
            $updateData['has_completed_mandatory_creation'] = false;
            $updateData['digital_activation_paid'] = false;
            $updateData['digital_product_uploaded'] = false;
        }

        if ($request->account_type === 'growth_seller') {
            $updateData['has_completed_mandatory_creation'] = false;
            $updateData['growth_activation_paid'] = false;
            $updateData['growth_listing_created'] = false;
        }

        $user->update($updateData);

        if ($request->account_type === 'earner' || $request->account_type === 'task_creator') {
            return redirect()->route('start-your-journey');
        }

        if ($request->account_type === 'freelancer') {
            return redirect()->route('onboarding.freelancer')->with('success', 'Freelancer onboarding complete. Please verify profile and create your first service to unlock the marketplace.');
        }

        if ($request->account_type === 'digital_seller') {
            return redirect()->route('onboarding.digital-product')->with('success', 'Digital product onboarding started. Upload your first product to unlock marketplace browsing.');
        }

        if ($request->account_type === 'growth_seller') {
            return redirect()->route('onboarding.growth')->with('success', 'Growth marketplace onboarding started. Create your first listing to unlock marketplace browsing.');
        }

        if ($request->account_type === 'buyer') {
            return redirect()->route('onboarding.buyer.categories')->with('success', 'Please select your preferred categories to personalize your marketplace experience.');
        }

        return redirect()->route('dashboard')->with('success', 'Onboarding complete.');
    }

    public function activateEarner(Request $request)
    {
        $user = Auth::user();
        $fee = SystemSetting::get('activation_fee', 1500);

        if ($user->wallet_balance < $fee) {
            return redirect()->route('start-your-journey')->with('error', 'Insufficient balance for earnings activation.');
        }

        DB::transaction(function () use ($user, $fee) {
            $user->decrement('wallet_balance', $fee);
            $user->update(['activation_paid' => true]);

            \App\Models\FinancialTransaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $fee,
                'description' => 'Earnings activation fee',
                'reference' => 'earn_activation_' . $user->id,
            ]);
        });

        return redirect()->route('start-your-journey')->with('success', 'Earnings activation successful. You now have access to Micro, UGC and Referral tasks.');
    }

    public function activateFreelancer(Request $request)
    {
        $user = Auth::user();
        // Freelancers get free activation
        $fee = 0;

        if ($fee > 0 && $user->wallet_balance < $fee) {
            return redirect()->route('onboarding.freelancer')->with('error', 'Insufficient balance for freelancer activation.');
        }

        if ($fee > 0) {
            DB::transaction(function () use ($user, $fee) {
                $user->decrement('wallet_balance', $fee);
                
                \App\Models\FinancialTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $fee,
                    'description' => 'Freelancer activation fee',
                    'reference' => 'freelancer_activation_' . $user->id,
                ]);
            });
        }
        
        $user->update(['freelancer_activation_paid' => true]);

        return redirect()->route('onboarding.freelancer')->with('success', 'Freelancer activation successful. Now complete your profile and create your first service.');
    }

    public function activateDigitalProduct(Request $request)
    {
        $user = Auth::user();
        // Digital sellers get free activation
        $fee = 0;

        if ($fee > 0 && $user->wallet_balance < $fee) {
            return redirect()->route('onboarding.digital-product')->with('error', 'Insufficient balance for digital product activation.');
        }

        if ($fee > 0) {
            DB::transaction(function () use ($user, $fee) {
                $user->decrement('wallet_balance', $fee);
                
                \App\Models\FinancialTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $fee,
                    'description' => 'Digital product activation fee',
                    'reference' => 'digital_activation_' . $user->id,
                ]);
            });
        }
        
        $user->update(['digital_activation_paid' => true]);

        return redirect()->route('onboarding.digital-product')->with('success', 'Digital product activation successful. Upload your first product next.');
    }

    public function activateGrowth(Request $request)
    {
        $user = Auth::user();
        // Growth sellers get free activation
        $fee = 0;

        if ($fee > 0 && $user->wallet_balance < $fee) {
            return redirect()->route('onboarding.growth')->with('error', 'Insufficient balance for growth marketplace activation.');
        }

        if ($fee > 0) {
            DB::transaction(function () use ($user, $fee) {
                $user->decrement('wallet_balance', $fee);
                
                \App\Models\FinancialTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $fee,
                    'description' => 'Growth marketplace activation fee',
                    'reference' => 'growth_activation_' . $user->id,
                ]);
            });
        }
        
        $user->update(['growth_activation_paid' => true]);

        return redirect()->route('onboarding.growth')->with('success', 'Growth marketplace activation successful. Create your first listing next.');
    }

    public function completeReferralTask(Request $request)
    {
        $user = Auth::user();

        if ($user->referral_task_completed) {
            return redirect()->route('start-your-journey')->with('info', 'Referral task already completed.');
        }

        $userWallet = $user->wallet;
        if (!$userWallet) {
            return redirect()->route('start-your-journey')->with('error', 'Wallet not available.');
        }

        $reward = 500;
        $userWallet->addWithdrawable($reward, 'referral_bonus', 'Earner referral onboarding task reward');

        $user->update(['referral_task_completed' => true]);

        \App\Models\FinancialTransaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $reward,
            'description' => 'Referral onboarding task reward',
            'reference' => 'referral_task_reward_' . $user->id,
        ]);

        return redirect()->route('start-your-journey')->with('success', 'Referral task completed; ₦500 credited to your wallet.');
    }

    public function skipReferralTask(Request $request)
    {
        $user = Auth::user();
        $user->update(['referral_task_skipped' => true]);

        return redirect()->route('start-your-journey')->with('info', 'Referral task skipped. You can complete it later from the dashboard.');
    }

    public function unlockEarnerFeature(Request $request)
    {
        $request->validate([
            'feature' => 'required|in:professional_services,digital_products,growth_marketplace,task_creation',
            'period' => 'nullable|in:initial,monthly,quarterly',
        ]);

        $user = Auth::user();

        $feature = $request->input('feature');
        $period = $request->input('period', 'initial');

        $amount = 1000;
        $months = 3;

        if ($period === 'monthly') {
            $amount = 500;
            $months = 1;
        } elseif ($period === 'quarterly') {
            $amount = 1000;
            $months = 3;
        }

        // Generate idempotency key to prevent duplicate payments
        $idempotencyKey = 'earner_' . $feature . '_' . $period . '_' . $user->id . '_' . time();
        
        // Check if there's already a pending unlock for this feature (within last 5 minutes)
        $existingRef = 'earner_feature_' . $feature . '_' . $user->id;
        $existingTx = \App\Models\FinancialTransaction::where('user_id', $user->id)
            ->where('reference', 'like', $existingRef . '%')
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();
        
        if ($existingTx) {
            return redirect()->route('dashboard')->with('info', 'This feature unlock is already being processed.');
        }

        if ($user->wallet_balance < $amount) {
            // Store pending feature unlock in session
            $pendingData = [
                'idempotency_key' => $idempotencyKey,
                'account_type' => 'earner',
                'feature' => $feature,
                'period' => $period,
                'amount' => $amount,
                'months' => $months,
                'unlock_method' => 'unlockEarnerFeature',
                'redirect_route' => 'start-your-journey',
                'created_at' => now()->toDateTimeString(),
            ];
            session(['pending_feature_unlock' => $pendingData]);
            
            // Calculate required amount
            $requiredAmount = $amount - $user->wallet_balance;
            
            // Redirect to deposit with success redirect back to complete the unlock
            return redirect()->route('wallet.deposit', [
                'required' => $requiredAmount,
            ])->with('deposit_success_redirect', route('onboarding.feature.unlock.complete'))
              ->with('info', 'Insufficient funds. Please deposit ₦' . number_format($requiredAmount) . ' to unlock this feature. Your selected feature will be unlocked after payment.');
        }

        // Process the unlock directly (sufficient funds) - with idempotency lock
        $processedKey = 'feature_unlock_processed_' . md5($idempotencyKey);
        if (cache()->has($processedKey)) {
            return redirect()->route('start-your-journey')->with('info', 'This feature unlock is already being processed.');
        }
        
        cache()->put($processedKey, true, now()->addMinutes(5));

        try {
            DB::transaction(function () use ($user, $feature, $amount, $months, $idempotencyKey) {
                $user->decrement('wallet_balance', $amount);
                $user->unlockEarnerFeature($feature, $months);

                \App\Models\FinancialTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => 'Earner feature unlock: ' . $feature,
                    'reference' => 'earner_feature_' . $feature . '_' . $user->id . '_' . time(),
                ]);
            });
            
            cache()->forget($processedKey);
            
            return redirect()->route('start-your-journey')->with('success', ucfirst(str_replace('_', ' ', $feature)) . ' unlocked for ' . $months . ' months.');
            
        } catch (\Exception $e) {
            cache()->forget($processedKey);
            throw $e;
        }
    }

    /**
     * Complete pending feature unlock after payment
     */
    public function completePendingFeatureUnlock(Request $request)
    {
        $pendingUnlock = session('pending_feature_unlock');
        
        if (!$pendingUnlock) {
            return redirect()->route('dashboard')->with('info', 'No pending feature unlock found.');
        }
        
        $user = Auth::user();
        
        // Verify the user has sufficient funds now
        $requiredAmount = $pendingUnlock['amount'];
        
        if ($user->wallet_balance < $requiredAmount) {
            // Still insufficient, redirect to deposit again
            $shortfall = $requiredAmount - $user->wallet_balance;
            return redirect()->route('wallet.deposit', [
                'required' => $shortfall,
            ])->with('deposit_success_redirect', route('onboarding.feature.unlock.complete'))
              ->with('error', 'Insufficient funds. Please deposit ₦' . number_format($shortfall) . ' more.');
        }
        
        // Complete the unlock based on account type
        $redirectRoute = $pendingUnlock['redirect_route'] ?? 'dashboard';
        
        // Use idempotency key to prevent duplicate processing
        $processedKey = 'feature_unlock_processed_' . md5($pendingUnlock['idempotency_key']);
        if (cache()->has($processedKey)) {
            return redirect()->route($redirectRoute)->with('info', 'This feature unlock is already being processed.');
        }
        
        cache()->put($processedKey, true, now()->addMinutes(5));

        try {
            DB::transaction(function () use ($user, $pendingUnlock) {
                $user->decrement('wallet_balance', $pendingUnlock['amount']);
                
                // Call the appropriate unlock method based on account type
                $accountType = $pendingUnlock['account_type'];
                switch ($accountType) {
                    case 'earner':
                        $user->unlockEarnerFeature($pendingUnlock['feature'], $pendingUnlock['months']);
                        break;
                    case 'buyer':
                        $user->unlockBuyerFeature($pendingUnlock['feature'], $pendingUnlock['months']);
                        break;
                    case 'task_creator':
                        $user->unlockTaskCreatorFeature($pendingUnlock['feature'], $pendingUnlock['months']);
                        break;
                    case 'freelancer':
                        $user->unlockFreelancerFeature($pendingUnlock['feature'], $pendingUnlock['months']);
                        break;
                    case 'digital_seller':
                        $user->unlockDigitalSellerFeature($pendingUnlock['feature'], $pendingUnlock['months']);
                        break;
                    case 'growth_seller':
                        $user->unlockGrowthSellerFeature($pendingUnlock['feature'], $pendingUnlock['months']);
                        break;
                }

                \App\Models\FinancialTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $pendingUnlock['amount'],
                    'description' => ucfirst($accountType) . ' feature unlock: ' . $pendingUnlock['feature'],
                    'reference' => $accountType . '_feature_' . $pendingUnlock['feature'] . '_' . $user->id . '_' . time(),
                ]);
            });
            
            // Clear pending unlock from session
            session()->forget('pending_feature_unlock');
            cache()->forget($processedKey);
            
            return redirect()->route($redirectRoute)->with('success', ucfirst(str_replace('_', ' ', $pendingUnlock['feature'])) . ' unlocked for ' . $pendingUnlock['months'] . ' months.');
            
        } catch (\Exception $e) {
            cache()->forget($processedKey);
            throw $e;
        }
    }

    /**
     * Unlock a buyer feature
     */
    public function unlockBuyerFeature(Request $request)
    {
        $request->validate([
            'feature' => 'required|in:professional_services,task_creation,available_tasks',
            'period' => 'nullable|in:initial,monthly,quarterly',
        ]);

        $user = Auth::user();

        $feature = $request->input('feature');
        $period = $request->input('period', 'initial');

        $amount = 1000;
        $months = 3;

        if ($period === 'monthly') {
            $amount = 500;
            $months = 1;
        } elseif ($period === 'quarterly') {
            $amount = 1000;
            $months = 3;
        }

        // Generate idempotency key to prevent duplicate payments
        $idempotencyKey = 'buyer_' . $feature . '_' . $period . '_' . $user->id . '_' . time();
        
        // Check if there's already a pending unlock for this feature (within last 5 minutes)
        $existingRef = 'buyer_feature_' . $feature . '_' . $user->id;
        $existingTx = \App\Models\FinancialTransaction::where('user_id', $user->id)
            ->where('reference', 'like', $existingRef . '%')
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();
        
        if ($existingTx) {
            return redirect()->route('dashboard')->with('info', 'This feature unlock is already being processed.');
        }

        if ($user->wallet_balance < $amount) {
            // Store pending feature unlock in session
            $pendingData = [
                'idempotency_key' => $idempotencyKey,
                'account_type' => 'buyer',
                'feature' => $feature,
                'period' => $period,
                'amount' => $amount,
                'months' => $months,
                'unlock_method' => 'unlockBuyerFeature',
                'redirect_route' => 'dashboard',
                'created_at' => now()->toDateTimeString(),
            ];
            session(['pending_feature_unlock' => $pendingData]);
            
            // Calculate required amount
            $requiredAmount = $amount - $user->wallet_balance;
            
            // Redirect to deposit with success redirect back to complete the unlock
            return redirect()->route('wallet.deposit', [
                'required' => $requiredAmount,
            ])->with('deposit_success_redirect', route('onboarding.feature.unlock.complete'))
              ->with('info', 'Insufficient funds. Please deposit ₦' . number_format($requiredAmount) . ' to unlock this feature. Your selected feature will be unlocked after payment.');
        }

        // Process the unlock directly (sufficient funds) - with idempotency lock
        $processedKey = 'feature_unlock_processed_' . md5($idempotencyKey);
        if (cache()->has($processedKey)) {
            return redirect()->route('dashboard')->with('info', 'This feature unlock is already being processed.');
        }
        
        cache()->put($processedKey, true, now()->addMinutes(5));

        try {
            DB::transaction(function () use ($user, $feature, $amount, $months, $idempotencyKey) {
                $user->decrement('wallet_balance', $amount);
                $user->unlockBuyerFeature($feature, $months);

                \App\Models\FinancialTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => 'Buyer feature unlock: ' . $feature,
                    'reference' => 'buyer_feature_' . $feature . '_' . $user->id . '_' . time(),
                ]);
            });
            
            cache()->forget($processedKey);
            
            return redirect()->route('dashboard')->with('success', ucfirst(str_replace('_', ' ', $feature)) . ' unlocked for ' . $months . ' months.');
            
        } catch (\Exception $e) {
            cache()->forget($processedKey);
            throw $e;
        }
    }

    /**
     * Unlock a task creator feature
     */
    public function unlockTaskCreatorFeature(Request $request)
    {
        $request->validate([
            'feature' => 'required|in:available_tasks,professional_services,growth_listings,digital_products',
            'period' => 'nullable|in:initial,monthly,quarterly',
        ]);

        $user = Auth::user();

        $feature = $request->input('feature');
        $period = $request->input('period', 'initial');

        $amount = 1000;
        $months = 3;

        if ($period === 'monthly') {
            $amount = 500;
            $months = 1;
        } elseif ($period === 'quarterly') {
            $amount = 1000;
            $months = 3;
        }

        // Generate idempotency key to prevent duplicate payments
        $idempotencyKey = 'task_creator_' . $feature . '_' . $period . '_' . $user->id . '_' . time();
        
        // Check if there's already a pending unlock for this feature (within last 5 minutes)
        $existingRef = 'task_creator_feature_' . $feature . '_' . $user->id;
        $existingTx = \App\Models\FinancialTransaction::where('user_id', $user->id)
            ->where('reference', 'like', $existingRef . '%')
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();
        
        if ($existingTx) {
            return redirect()->route('dashboard')->with('info', 'This feature unlock is already being processed.');
        }

        if ($user->wallet_balance < $amount) {
            // Store pending feature unlock in session
            $pendingData = [
                'idempotency_key' => $idempotencyKey,
                'account_type' => 'task_creator',
                'feature' => $feature,
                'period' => $period,
                'amount' => $amount,
                'months' => $months,
                'unlock_method' => 'unlockTaskCreatorFeature',
                'redirect_route' => 'dashboard',
                'created_at' => now()->toDateTimeString(),
            ];
            session(['pending_feature_unlock' => $pendingData]);
            
            // Calculate required amount
            $requiredAmount = $amount - $user->wallet_balance;
            
            // Redirect to deposit with success redirect back to complete the unlock
            return redirect()->route('wallet.deposit', [
                'required' => $requiredAmount,
            ])->with('deposit_success_redirect', route('onboarding.feature.unlock.complete'))
              ->with('info', 'Insufficient funds. Please deposit ₦' . number_format($requiredAmount) . ' to unlock this feature. Your selected feature will be unlocked after payment.');
        }

        // Process the unlock directly (sufficient funds) - with idempotency lock
        $processedKey = 'feature_unlock_processed_' . md5($idempotencyKey);
        if (cache()->has($processedKey)) {
            return redirect()->route('dashboard')->with('info', 'This feature unlock is already being processed.');
        }
        
        cache()->put($processedKey, true, now()->addMinutes(5));

        try {
            DB::transaction(function () use ($user, $feature, $amount, $months, $idempotencyKey) {
                $user->decrement('wallet_balance', $amount);
                $user->unlockTaskCreatorFeature($feature, $months);

                \App\Models\FinancialTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => 'Task Creator feature unlock: ' . $feature,
                    'reference' => 'task_creator_feature_' . $feature . '_' . $user->id . '_' . time(),
                ]);
            });
            
            cache()->forget($processedKey);
            
            return redirect()->route('dashboard')->with('success', ucfirst(str_replace('_', ' ', $feature)) . ' unlocked for ' . $months . ' months.');
            
        } catch (\Exception $e) {
            cache()->forget($processedKey);
            throw $e;
        }
    }

    /**
     * Unlock a freelancer feature
     */
    public function unlockFreelancerFeature(Request $request)
    {
        $request->validate([
            'feature' => 'required|in:task_creation,available_tasks,growth_listings,digital_products',
            'period' => 'nullable|in:initial,monthly,quarterly',
        ]);

        $user = Auth::user();

        $feature = $request->input('feature');
        $period = $request->input('period', 'initial');

        $amount = 1000;
        $months = 3;

        if ($period === 'monthly') {
            $amount = 500;
            $months = 1;
        } elseif ($period === 'quarterly') {
            $amount = 1000;
            $months = 3;
        }

        // Generate idempotency key to prevent duplicate payments
        $idempotencyKey = 'freelancer_' . $feature . '_' . $period . '_' . $user->id . '_' . time();
        
        // Check if there's already a pending unlock
        $existingRef = 'freelancer_feature_' . $feature . '_' . $user->id;
        $existingTx = \App\Models\FinancialTransaction::where('user_id', $user->id)
            ->where('reference', 'like', $existingRef . '%')
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();
        
        if ($existingTx) {
            return redirect()->route('dashboard')->with('info', 'This feature unlock is already being processed.');
        }

        if ($user->wallet_balance < $amount) {
            // Store pending unlock in session
            $pendingData = [
                'idempotency_key' => $idempotencyKey,
                'account_type' => 'freelancer',
                'feature' => $feature,
                'period' => $period,
                'amount' => $amount,
                'months' => $months,
                'unlock_method' => 'unlockFreelancerFeature',
                'redirect_route' => 'dashboard',
                'created_at' => now()->toDateTimeString(),
            ];
            session(['pending_feature_unlock' => $pendingData]);
            
            $requiredAmount = $amount - $user->wallet_balance;
            
            return redirect()->route('wallet.deposit', [
                'required' => $requiredAmount,
            ])->with('deposit_success_redirect', route('onboarding.feature.unlock.complete'))
              ->with('info', 'Insufficient funds. Please deposit ₦' . number_format($requiredAmount) . ' to unlock this feature.');
        }

        // Process directly with idempotency lock
        $processedKey = 'feature_unlock_processed_' . md5($idempotencyKey);
        if (cache()->has($processedKey)) {
            return redirect()->route('dashboard')->with('info', 'This feature unlock is already being processed.');
        }
        
        cache()->put($processedKey, true, now()->addMinutes(5));

        try {
            DB::transaction(function () use ($user, $feature, $amount, $months, $idempotencyKey) {
                $user->decrement('wallet_balance', $amount);
                $user->unlockFreelancerFeature($feature, $months);

                \App\Models\FinancialTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => 'Freelancer feature unlock: ' . $feature,
                    'reference' => 'freelancer_feature_' . $feature . '_' . $user->id . '_' . time(),
                ]);
            });
            
            cache()->forget($processedKey);
            
            return redirect()->route('dashboard')->with('success', ucfirst(str_replace('_', ' ', $feature)) . ' unlocked for ' . $months . ' months.');
            
        } catch (\Exception $e) {
            cache()->forget($processedKey);
            throw $e;
        }
    }

    /**
     * Unlock a digital seller feature
     */
    public function unlockDigitalSellerFeature(Request $request)
    {
        $request->validate([
            'feature' => 'required|in:task_creation,available_tasks,professional_services,growth_listings',
            'period' => 'nullable|in:initial,monthly,quarterly',
        ]);

        $user = Auth::user();

        $feature = $request->input('feature');
        $period = $request->input('period', 'initial');

        $amount = 1000;
        $months = 3;

        if ($period === 'monthly') {
            $amount = 500;
            $months = 1;
        } elseif ($period === 'quarterly') {
            $amount = 1000;
            $months = 3;
        }

        // Generate idempotency key
        $idempotencyKey = 'digital_seller_' . $feature . '_' . $period . '_' . $user->id . '_' . time();
        
        // Check existing transaction
        $existingRef = 'digital_seller_feature_' . $feature . '_' . $user->id;
        $existingTx = \App\Models\FinancialTransaction::where('user_id', $user->id)
            ->where('reference', 'like', $existingRef . '%')
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();
        
        if ($existingTx) {
            return redirect()->route('dashboard')->with('info', 'This feature unlock is already being processed.');
        }

        if ($user->wallet_balance < $amount) {
            $pendingData = [
                'idempotency_key' => $idempotencyKey,
                'account_type' => 'digital_seller',
                'feature' => $feature,
                'period' => $period,
                'amount' => $amount,
                'months' => $months,
                'unlock_method' => 'unlockDigitalSellerFeature',
                'redirect_route' => 'dashboard',
                'created_at' => now()->toDateTimeString(),
            ];
            session(['pending_feature_unlock' => $pendingData]);
            
            $requiredAmount = $amount - $user->wallet_balance;
            
            return redirect()->route('wallet.deposit', [
                'required' => $requiredAmount,
            ])->with('deposit_success_redirect', route('onboarding.feature.unlock.complete'))
              ->with('info', 'Insufficient funds. Please deposit ₦' . number_format($requiredAmount) . ' to unlock this feature.');
        }

        $processedKey = 'feature_unlock_processed_' . md5($idempotencyKey);
        if (cache()->has($processedKey)) {
            return redirect()->route('dashboard')->with('info', 'This feature unlock is already being processed.');
        }
        
        cache()->put($processedKey, true, now()->addMinutes(5));

        try {
            DB::transaction(function () use ($user, $feature, $amount, $months, $idempotencyKey) {
                $user->decrement('wallet_balance', $amount);
                $user->unlockDigitalSellerFeature($feature, $months);

                \App\Models\FinancialTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => 'Digital Seller feature unlock: ' . $feature,
                    'reference' => 'digital_seller_feature_' . $feature . '_' . $user->id . '_' . time(),
                ]);
            });
            
            cache()->forget($processedKey);
            
            return redirect()->route('dashboard')->with('success', ucfirst(str_replace('_', ' ', $feature)) . ' unlocked for ' . $months . ' months.');
            
        } catch (\Exception $e) {
            cache()->forget($processedKey);
            throw $e;
        }
    }

    /**
     * Unlock a growth seller feature
     */
    public function unlockGrowthSellerFeature(Request $request)
    {
        $request->validate([
            'feature' => 'required|in:task_creation,available_tasks,professional_services,digital_products',
            'period' => 'nullable|in:initial,monthly,quarterly',
        ]);

        $user = Auth::user();

        $feature = $request->input('feature');
        $period = $request->input('period', 'initial');

        $amount = 1000;
        $months = 3;

        if ($period === 'monthly') {
            $amount = 500;
            $months = 1;
        } elseif ($period === 'quarterly') {
            $amount = 1000;
            $months = 3;
        }

        // Generate idempotency key
        $idempotencyKey = 'growth_seller_' . $feature . '_' . $period . '_' . $user->id . '_' . time();
        
        // Check existing transaction
        $existingRef = 'growth_seller_feature_' . $feature . '_' . $user->id;
        $existingTx = \App\Models\FinancialTransaction::where('user_id', $user->id)
            ->where('reference', 'like', $existingRef . '%')
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();
        
        if ($existingTx) {
            return redirect()->route('dashboard')->with('info', 'This feature unlock is already being processed.');
        }

        if ($user->wallet_balance < $amount) {
            $pendingData = [
                'idempotency_key' => $idempotencyKey,
                'account_type' => 'growth_seller',
                'feature' => $feature,
                'period' => $period,
                'amount' => $amount,
                'months' => $months,
                'unlock_method' => 'unlockGrowthSellerFeature',
                'redirect_route' => 'dashboard',
                'created_at' => now()->toDateTimeString(),
            ];
            session(['pending_feature_unlock' => $pendingData]);
            
            $requiredAmount = $amount - $user->wallet_balance;
            
            return redirect()->route('wallet.deposit', [
                'required' => $requiredAmount,
            ])->with('deposit_success_redirect', route('onboarding.feature.unlock.complete'))
              ->with('info', 'Insufficient funds. Please deposit ₦' . number_format($requiredAmount) . ' to unlock this feature.');
        }

        $processedKey = 'feature_unlock_processed_' . md5($idempotencyKey);
        if (cache()->has($processedKey)) {
            return redirect()->route('dashboard')->with('info', 'This feature unlock is already being processed.');
        }
        
        cache()->put($processedKey, true, now()->addMinutes(5));

        try {
            DB::transaction(function () use ($user, $feature, $amount, $months, $idempotencyKey) {
                $user->decrement('wallet_balance', $amount);
                $user->unlockGrowthSellerFeature($feature, $months);

                \App\Models\FinancialTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => 'Growth Seller feature unlock: ' . $feature,
                    'reference' => 'growth_seller_feature_' . $feature . '_' . $user->id . '_' . time(),
                ]);
            });
            
            cache()->forget($processedKey);
            
            return redirect()->route('dashboard')->with('success', ucfirst(str_replace('_', ' ', $feature)) . ' unlocked for ' . $months . ' months.');
            
        } catch (\Exception $e) {
            cache()->forget($processedKey);
            throw $e;
        }
    }

    // Done: earner-specific onboarding removed. Task creator and buyer/freelancer now use start-your-journey/dashboard pathways.
    public function taskCreatorOnboarding()
    {
        $user = Auth::user();
        if (!$user->onboarding_completed) {
            $user->update(['onboarding_completed' => true, 'onboarding_completed_at' => now()]);
        }
        return view('onboarding.task-creator', compact('user'));
    }

    public function freelancerOnboarding()
    {
        $user = Auth::user();
        if (!$user->onboarding_completed) {
            $user->update(['onboarding_completed' => true, 'onboarding_completed_at' => now()]);
        }
        return view('onboarding.freelancer', compact('user'));
    }

    public function buyerOnboarding()
    {
        $user = Auth::user();
        // For buyers, show category selection page instead of marking complete
        return redirect()->route('onboarding.buyer.categories');
    }

    public function digitalProductOnboarding()
    {
        $user = Auth::user();
        if (!$user->onboarding_completed) {
            $user->update(['onboarding_completed' => true, 'onboarding_completed_at' => now()]);
        }

        $wallet = $user->wallet;

       // Check activation status
        $isActivated = $wallet && $wallet->is_activated;
        return view('onboarding.digital-product', compact('user', 'isActivated'));
    }

    public function growthOnboarding()
    {
        $user = Auth::user();
        if (!$user->onboarding_completed) {
            $user->update(['onboarding_completed' => true, 'onboarding_completed_at' => now()]);
        }
        return view('onboarding.growth', compact('user'));
    }

    /**
     * Show the feature unlock page for any role
     */
    public function showFeatureUnlock()
    {
        $user = Auth::user();
        $accountType = $user->account_type;

        // Build features based on account type
        $features = [];
        $unlockRoute = '';

        switch ($accountType) {
            case 'buyer':
                $features = [
                    'professional_services' => [
                        'label' => 'Professional Services',
                        'unlocked' => $user->hasBuyerFeature('professional_services'),
                        'expires' => $user->getTaskCreatorFeatureExpiry('professional_services'),
                    ],
                    'task_creation' => [
                        'label' => 'Task Creation',
                        'unlocked' => $user->hasBuyerFeature('task_creation'),
                        'expires' => $user->getBuyerFeatureExpiry('task_creation'),
                    ],
                    'available_tasks' => [
                        'label' => 'Available Tasks',
                        'unlocked' => $user->hasBuyerFeature('available_tasks'),
                        'expires' => $user->getBuyerFeatureExpiry('available_tasks'),
                    ],
                ];
                $unlockRoute = route('onboarding.buyer.feature.unlock');
                break;

            case 'earner':
                $features = [
                    'professional_services' => [
                        'label' => 'Professional Services',
                        'unlocked' => $user->hasTaskCreatorFeature('professional_services'),
                        'expires' => $user->getTaskCreatorFeatureExpiry('professional_services'),
                    ],
                    'digital_products' => [
                        'label' => 'Digital Products',
                        'unlocked' => $user->hasEarnerFeature('digital_products'),
                        'expires' => $user->getEarnerFeatureExpiry('digital_products'),
                    ],
                    'growth_marketplace' => [
                        'label' => 'Growth Marketplace',
                        'unlocked' => $user->hasEarnerFeature('growth_marketplace'),
                        'expires' => $user->getEarnerFeatureExpiry('growth_marketplace'),
                    ],
                    'task_creation' => [
                        'label' => 'Task Creation',
                        'unlocked' => $user->hasEarnerFeature('task_creation'),
                        'expires' => $user->getEarnerFeatureExpiry('task_creation'),
                    ],
                ];
                $unlockRoute = route('onboarding.earn.feature.unlock');
                break;

            case 'task_creator':
                $features = [
                    'available_tasks' => [
                        'label' => 'Available Tasks',
                        'unlocked' => $user->hasTaskCreatorFeature('available_tasks'),
                        'expires' => $user->getTaskCreatorFeatureExpiry('available_tasks'),
                    ],
                    'professional_services' => [
                        'label' => 'Professional Services',
                        'unlocked' => $user->hasTaskCreatorFeature('professional_services'),
                        'expires' => $user->getTaskCreatorFeatureExpiry('professional_services'),
                    ],
                    'growth_listings' => [
                        'label' => 'Growth Listings',
                        'unlocked' => $user->hasTaskCreatorFeature('growth_listings'),
                        'expires' => $user->getTaskCreatorFeatureExpiry('growth_listings'),
                    ],
                    'digital_products' => [
                        'label' => 'Digital Products',
                        'unlocked' => $user->hasTaskCreatorFeature('digital_products'),
                        'expires' => $user->getTaskCreatorFeatureExpiry('digital_products'),
                    ],
                ];
                $unlockRoute = route('onboarding.task-creator.feature.unlock');
                break;

            case 'freelancer':
                $features = [
                    'task_creation' => [
                        'label' => 'Task Creation',
                        'unlocked' => $user->hasFreelancerFeature('task_creation'),
                        'expires' => $user->getFreelancerFeatureExpiry('task_creation'),
                    ],
                    'available_tasks' => [
                        'label' => 'Available Tasks',
                        'unlocked' => $user->hasFreelancerFeature('available_tasks'),
                        'expires' => $user->getFreelancerFeatureExpiry('available_tasks'),
                    ],
                    'growth_listings' => [
                        'label' => 'Growth Listings',
                        'unlocked' => $user->hasFreelancerFeature('growth_listings'),
                        'expires' => $user->getFreelancerFeatureExpiry('growth_listings'),
                    ],
                    'digital_products' => [
                        'label' => 'Digital Products',
                        'unlocked' => $user->hasFreelancerFeature('digital_products'),
                        'expires' => $user->getFreelancerFeatureExpiry('digital_products'),
                    ],
                ];
                $unlockRoute = route('onboarding.freelancer.feature.unlock');
                break;

            case 'digital_seller':
                $features = [
                    'task_creation' => [
                        'label' => 'Task Creation',
                        'unlocked' => $user->hasDigitalSellerFeature('task_creation'),
                        'expires' => $user->getDigitalSellerFeatureExpiry('task_creation'),
                    ],
                    'available_tasks' => [
                        'label' => 'Available Tasks',
                        'unlocked' => $user->hasDigitalSellerFeature('available_tasks'),
                        'expires' => $user->getDigitalSellerFeatureExpiry('available_tasks'),
                    ],
                    'professional_services' => [
                        'label' => 'Professional Services',
                        'unlocked' => $user->hasDigitalSellerFeature('professional_services'),
                        'expires' => $user->getDigitalSellerFeatureExpiry('professional_services'),
                    ],
                    'growth_listings' => [
                        'label' => 'Growth Listings',
                        'unlocked' => $user->hasDigitalSellerFeature('growth_listings'),
                        'expires' => $user->getDigitalSellerFeatureExpiry('growth_listings'),
                    ],
                ];
                $unlockRoute = route('onboarding.digital-seller.feature.unlock');
                break;

            case 'growth_seller':
                $features = [
                    'task_creation' => [
                        'label' => 'Task Creation',
                        'unlocked' => $user->hasGrowthSellerFeature('task_creation'),
                        'expires' => $user->getGrowthSellerFeatureExpiry('task_creation'),
                    ],
                    'available_tasks' => [
                        'label' => 'Available Tasks',
                        'unlocked' => $user->hasGrowthSellerFeature('available_tasks'),
                        'expires' => $user->getGrowthSellerFeatureExpiry('available_tasks'),
                    ],
                    'professional_services' => [
                        'label' => 'Professional Services',
                        'unlocked' => $user->hasGrowthSellerFeature('professional_services'),
                        'expires' => $user->getGrowthSellerFeatureExpiry('professional_services'),
                    ],
                    'digital_products' => [
                        'label' => 'Digital Products',
                        'unlocked' => $user->hasGrowthSellerFeature('digital_products'),
                        'expires' => $user->getGrowthSellerFeatureExpiry('digital_products'),
                    ],
                ];
                $unlockRoute = route('onboarding.growth-seller.feature.unlock');
                break;

            default:
                return redirect()->route('dashboard');
        }

        return view('onboarding.feature-unlock', compact('features', 'unlockRoute', 'accountType'));
    }

    /**
     * Show buyer category selection page
     */
    public function buyerCategorySelection()
    {
        $user = Auth::user();

        // Get all marketplace categories grouped by type
        $professionalCategories = \App\Models\ProfessionalServiceCategory::where('is_active', true)->get();
        $digitalCategories = \App\Models\MarketplaceCategory::where('type', 'digital_product')
            ->where('is_active', true)
            ->get();
        $growthCategories = \App\Models\MarketplaceCategory::where('type', 'growth')
            ->where('is_active', true)
            ->get();
        $jobCategories = \App\Models\MarketplaceCategory::where('type', 'job')
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->get();

        $selectedCategories = $user->getBuyerCategories();

        return view('onboarding.buyer', compact(
            'professionalCategories',
            'digitalCategories',
            'growthCategories',
            'jobCategories',
            'selectedCategories'
        ));
    }

    /**
     * Store buyer's selected categories
     */
    public function storeBuyerCategories(Request $request)
    {
        // Get all valid category IDs from all category tables
        $professionalIds = \App\Models\ProfessionalServiceCategory::where('is_active', true)->pluck('id')->toArray();
        $digitalIds = \App\Models\MarketplaceCategory::where('type', 'digital_product')->where('is_active', true)->pluck('id')->toArray();
        $growthIds = \App\Models\MarketplaceCategory::where('type', 'growth')->where('is_active', true)->pluck('id')->toArray();
        $jobIds = \App\Models\MarketplaceCategory::where('type', 'job')->where('is_active', true)->pluck('id')->toArray();
        
        $validIds = array_merge($professionalIds, $digitalIds, $growthIds, $jobIds);

        $request->validate([
            'categories' => 'required|array|min:1',
            'categories.*' => 'integer',
        ], [
            'categories.required' => 'Please select at least one category.',
            'categories.min' => 'Please select at least one category.',
        ]);

        // Validate that all selected IDs exist in any category table
        foreach ($request->categories as $categoryId) {
            if (!in_array($categoryId, $validIds)) {
                return back()->withErrors(['categories' => 'Invalid category selected.']);
            }
        }

        $user = Auth::user();

        // Save selected categories
        $user->setBuyerCategories($request->categories);

        // Mark onboarding as completed
        $user->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Category preferences saved! You can now browse your personalized marketplace.');
    }


    /**
     * Show buyer category preferences form
     */
    public function buyerCategoriesForm()
    {
        $user = auth()->user();
        
        if ($user->account_type !== 'buyer') {
            return redirect()->route('dashboard')->with('error', 'This feature is only available for buyers.');
        }

        $professionalCategories = \App\Models\ProfessionalServiceCategory::where('is_active', true)->get();
        $digitalCategories = \App\Models\MarketplaceCategory::where('type', 'digital_product')
            ->where('is_active', true)
            ->get();
        $growthCategories = \App\Models\MarketplaceCategory::where('type', 'growth')
            ->where('is_active', true)
            ->get();
        $jobCategories = \App\Models\MarketplaceCategory::where('type', 'job')
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->get();

        $selectedCategories = $user->getBuyerCategories();

        return view('settings.buyer-categories', compact(
            'professionalCategories',
            'digitalCategories',
            'growthCategories',
            'jobCategories',
            'selectedCategories'
        ));
    }

    /**
     * Update buyer category preferences
     */
    public function updateBuyerCategories(Request $request)
    {
        $user = auth()->user();
        
        if ($user->account_type !== 'buyer') {
            return redirect()->route('dashboard')->with('error', 'This feature is only available for buyers.');
        }

        // Get all valid category IDs from all category tables
        $professionalIds = \App\Models\ProfessionalServiceCategory::where('is_active', true)->pluck('id')->toArray();
        $digitalIds = \App\Models\MarketplaceCategory::where('type', 'digital_product')->where('is_active', true)->pluck('id')->toArray();
        $growthIds = \App\Models\MarketplaceCategory::where('type', 'growth')->where('is_active', true)->pluck('id')->toArray();
        $jobIds = \App\Models\MarketplaceCategory::where('type', 'job')->where('is_active', true)->pluck('id')->toArray();
        
        $validIds = array_merge($professionalIds, $digitalIds, $growthIds, $jobIds);

        $request->validate([
            'categories' => 'required|array|min:1',
            'categories.*' => 'integer',
        ], [
            'categories.required' => 'Please select at least one category.',
            'categories.min' => 'Please select at least one category.',
        ]);

        // Validate that all selected IDs exist in any category table
        foreach ($request->categories as $categoryId) {
            if (!in_array($categoryId, $validIds)) {
                return back()->withErrors(['categories' => 'Invalid category selected.']);
            }
        }

        $user->setBuyerCategories($request->categories);

        return redirect()->back()->with('success', 'Category preferences updated successfully!');
    }
}
