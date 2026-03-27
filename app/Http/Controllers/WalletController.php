<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\WalletLedger;
use App\Models\User;
use App\Models\Referral;
use App\Models\SystemSetting;
use App\Services\SwiftKudiService;
use App\Services\RevenueAggregator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    protected $earnDeskService;

    public function __construct(SwiftKudiService $earnDeskService)
    {
        $this->earnDeskService = $earnDeskService;
    }

    /**
     * Display wallet dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get or create wallet with error handling for missing columns
        try {
            $wallet = $user->wallet ?? Wallet::create([
                'user_id' => $user->id,
                'withdrawable_balance' => 0,
                'promo_credit_balance' => 0,
                'total_earned' => 0,
                'total_spent' => 0,
                'pending_balance' => 0,
                'escrow_balance' => 0,
            ]);
        } catch (\Exception $e) {
            Log::warning('Wallet creation failed, trying without earning categories', ['error' => $e->getMessage()]);
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'withdrawable_balance' => 0,
                    'promo_credit_balance' => 0,
                    'total_earned' => 0,
                    'total_spent' => 0,
                    'pending_balance' => 0,
                    'escrow_balance' => 0,
                ]
            );
        }

        // Get transactions
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get recent transactions for the sidebar
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get withdrawals
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get ledger entries
        $ledgerEntries = WalletLedger::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate earnings stats
        $stats = [
            'total_earned' => $wallet->total_earned,
            'total_spent' => $wallet->total_spent,
            'pending_balance' => $wallet->pending_balance,
            'can_withdraw' => $user instanceof User ? $user->canWithdraw() : false,
            'minimum_withdrawal' => User::getMinimumWithdrawal(),
        ];

        return view('wallet.index', compact(
            'wallet',
            'transactions',
            'recentTransactions',
            'withdrawals',
            'ledgerEntries',
            'stats'
        ));
    }

    /**
     * Display activation page
     */
    public function activate()
    {
        $user = Auth::user();
        
        try {
            $wallet = $user->wallet ?? Wallet::create([
                'user_id' => $user->id,
                'withdrawable_balance' => 0,
                'promo_credit_balance' => 0,
                'total_earned' => 0,
                'total_spent' => 0,
                'pending_balance' => 0,
                'escrow_balance' => 0,
            ]);
        } catch (\Exception $e) {
            // If wallet creation fails (e.g., missing columns), try without the new columns
            Log::warning('Wallet creation failed, trying without earning categories', ['error' => $e->getMessage()]);
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'withdrawable_balance' => 0,
                    'promo_credit_balance' => 0,
                    'total_earned' => 0,
                    'total_spent' => 0,
                    'pending_balance' => 0,
                    'escrow_balance' => 0,
                ]
            );
        }

        $isActivated = $wallet->is_activated ?? false;
        $activationFeeEnabled = SystemSetting::isCompulsoryActivationFee();

        // Check if user was referred. Prefer explicit referred_by relation but fall back to Referral records
        $referredBy = $user->referredBy;
        if (!$referredBy) {
            // First, try to find a referral record linked to this user by id or email
            $referralRecord = Referral::where('referred_user_id', $user->id)
                ->orWhere('referred_email', $user->email)
                ->first();

            // If not found, check for any session-stored referral code (in case user came via /ref/{code} but registration didn't persist it)
            if (!$referralRecord) {
                $sessionCode = session('referral_code');
                if ($sessionCode) {
                    $referralRecord = Referral::where('referral_code', $sessionCode)->first();
                }
            }

            if ($referralRecord) {
                $referredBy = $referralRecord->user;
            }
        }

        $activationFee = SystemSetting::getActivationFeeForUser(false);
        $referredActivationFee = SystemSetting::getActivationFeeForUser(true);
        // Activation fee only applies to earners; non-earners get free activation
        $isEarner = $user->account_type === 'earner';
        $actualFee = ($activationFeeEnabled && $isEarner) ? ($referredBy ? $referredActivationFee : $activationFee) : 0;

        return view('wallet.activate', compact(
            'wallet',
            'isActivated',
            'activationFee',
            'referredActivationFee',
            'actualFee',
            'referredBy',
            'activationFeeEnabled'
        ));
    }

    /**
     * Process activation
     */
    public function processActivation(Request $request)
    {
        $user = $request->user() ?? Auth::user();
        // Determine referrer: use relation first, then fallback to Referral record if present
        $referrer = $user->referredBy;
        if (!$referrer) {
            $referralRecord = Referral::where('referred_user_id', $user->id)
                ->orWhere('referred_email', $user->email)
                ->first();

            // also check session code as a last resort
            if (!$referralRecord) {
                $sessionCode = session('referral_code');
                if ($sessionCode) {
                    $referralRecord = Referral::where('referral_code', $sessionCode)->first();
                }
            }

            if ($referralRecord) {
                $referrer = $referralRecord->user;
            }
        }

        try {
            $result = $this->earnDeskService->activateUser($user, $referrer);
        } catch (\Exception $e) {
            Log::error('Activation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('wallet.activate')
                ->with('error', 'Activation failed: ' . $e->getMessage())
                ->withInput();
        }

        if ($result['success']) {
            // Check if mandatory task creation gate is enabled
            $gateEnabled = \App\Models\SystemSetting::get('mandatory_task_creation_enabled', true);
            if($user->account_type === 'task_creator' && $gateEnabled) {
            if ($gateEnabled) {
                // Redirect to start-your-journey page for new activation
                return redirect()->route('start-your-journey')
                    ->with('success', $result['message'] . ' Now create your first campaign to unlock task creation!');
            }
            }
            
            return redirect()->route('dashboard')
                ->with('success', $result['message']);
        }

        // If user needs to deposit, redirect to deposit page
        if (isset($result['needs_deposit']) && $result['needs_deposit']) {
            return redirect()->route('wallet.deposit')
                ->with('error', $result['message']);
        }

        return redirect()->route('wallet.activate')
            ->with('error', $result['message'])
            ->withInput();
    }

    /**
     * Skip activation and continue using platform with limited features.
     */
    public function skipActivation(Request $request)
    {
        session([
            'activation_skipped_at' => now()->toDateTimeString(),
            'activation_skip_notice_dismissed' => true,
        ]);

        $redirectTo = $request->input('redirect_to');
        if (!$redirectTo || !filter_var($redirectTo, FILTER_VALIDATE_URL)) {
            return redirect()->route('dashboard')
                ->with('info', 'You can continue without activation. Activate anytime to unlock withdrawals and full earning access.');
        }

        return redirect()->to($redirectTo)
            ->with('info', 'You can continue without activation. Activate anytime to unlock withdrawals and full earning access.');
    }

    /**
     * Display deposit form or process deposit
     */
    public function deposit(Request $request)
    {
        $user = Auth::user();
        
        // Get or create wallet
        $wallet = $user->wallet ?? Wallet::create([
            'user_id' => $user->id,
            'withdrawable_balance' => 0,
            'promo_credit_balance' => 0,
            'total_earned' => 0,
            'total_spent' => 0,
            'pending_balance' => 0,
            'escrow_balance' => 0,
        ]);

        // Check for required amount from task creation redirect
        $requiredAmount = $request->query('required');
        if ($requiredAmount) {
            session(['insufficient_balance_required' => $requiredAmount]);
        }

        // Handle GET request - show deposit form
        if ($request->isMethod('GET')) {
            return view('wallet.deposit', compact('wallet'));
        }

        // Handle POST request - redirect to payment gateway
        $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        // Store deposit context for redirect after payment
        if ($redirectRoute = session('deposit_success_redirect')) {
            session(['payment_success_redirect' => $redirectRoute]);
        } elseif (session()->has('task_creation_data')) {
            session(['payment_success_redirect' => route('tasks.create.resume')]);
        }

        // Redirect to payment initialization
        return redirect()->route('payment.initialize', [
            'amount' => $request->amount,
            'currency' => $request->input('currency', 'NGN'),
        ]);
    }

    /**
     * Display withdrawal form
     */
    public function withdraw()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return redirect()->route('wallet.index')
                ->with('error', 'Wallet not found');
        }

        $minimumWithdrawal = User::getMinimumWithdrawal();
        $canWithdraw = $user instanceof User ? $user->canWithdraw() : false;

        return view('wallet.withdraw', compact(
            'wallet',
            'minimumWithdrawal',
            'canWithdraw'
        ));
    }

    /**
     * Process withdrawal request
     */
    public function processWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'method' => 'required|in:bank,usdt',
            'instant' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        if (!$user instanceof User) {
            return redirect()->route('login')->with('error', 'Authentication required.');
        }

        $wallet = $user->wallet;
        if (!$wallet) {
            return redirect()->route('wallet.index')->with('error', 'Wallet not found');
        }

        if (!$user->canWithdraw()) {
            return redirect()->route('wallet.withdraw')->with('error', 'You are not eligible to withdraw yet.');
        }

        $amount = floatval($request->amount);
        $method = $request->method;
        $instant = $request->boolean('instant', false);

        $result = $wallet->processWithdrawal($amount, $instant, $method);

        if ($result['success']) {
            $withdrawalId = (int) ($result['withdrawal_id'] ?? 0);
            $withdrawal = $withdrawalId > 0 ? \App\Models\Withdrawal::find($withdrawalId) : null;

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $user,
                'Withdrawal Request Received',
                'Your withdrawal request has been received and is being processed.',
                \App\Models\Notification::TYPE_WITHDRAWAL,
                [
                    'withdrawal_id' => $withdrawalId,
                    'amount' => $result['formatted']['amount'] ?? ('₦' . number_format($result['amount'] ?? 0, 2)),
                    'net_amount' => $result['formatted']['net'] ?? ('₦' . number_format($result['net_amount'] ?? 0, 2)),
                    'method' => strtoupper($method),
                    'action_url' => route('wallet.index'),
                ],
                'notify_withdrawal',
                true
            );

            $threshold = (float) \App\Models\SystemSetting::getNumber('large_withdrawal_threshold', 50000);
            $largeWithdrawalAlertEnabled = \App\Models\SystemSetting::getBool('notify_large_withdrawal', true);
            if ($largeWithdrawalAlertEnabled && $withdrawal && (float) $withdrawal->amount >= $threshold) {
                app(\App\Services\NotificationDispatchService::class)->notifyAdmins(
                    'Large Withdrawal Alert',
                    'A large withdrawal request of ₦' . number_format((float) $withdrawal->amount, 2) . ' was submitted.',
                    [
                        'withdrawal_id' => $withdrawal->id,
                        'user_id' => $user->id,
                        'amount' => '₦' . number_format((float) $withdrawal->amount, 2),
                        'action_url' => route('admin.withdrawals'),
                    ],
                    null,
                    false
                );
            }

            return redirect()->route('wallet.index')
                ->with('success', $result['message'] . ' Net amount: ₦' . number_format($result['net_amount'], 2));
        }

        return redirect()->route('wallet.withdraw')
            ->with('error', $result['message'])
            ->withInput();
    }

    /**
     * Get wallet balance (API)
     */
    public function balance(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'withdrawable' => $wallet->withdrawable_balance,
                'promo_credit' => $wallet->promo_credit_balance,
                'total' => $wallet->withdrawable_balance + $wallet->promo_credit_balance,
                'escrow' => $wallet->escrow_balance,
                'is_activated' => $wallet->is_activated,
                'formatted' => $wallet->getFormattedBalance(),
            ],
        ]);
    }

    /**
     * Get transaction history (API)
     */
    public function transactions(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type');
        $status = $request->get('status');

        $query = Transaction::where('user_id', $user->id);

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Add promo credit (admin only - for bonuses, streaks, etc.)
     */
    public function addPromoCredit(Request $request)
    {
        // This would typically be admin-only
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return redirect()->route('wallet.index')
                ->with('error', 'Wallet not found');
        }

        $wallet->addPromoCredit($request->amount);

        WalletLedger::createEntry(
            $wallet,
            WalletLedger::TYPE_PROMO_CREDIT,
            $request->amount,
            $wallet->withdrawable_balance,
            $wallet->withdrawable_balance,
            $wallet->promo_credit_balance - $request->amount,
            $wallet->promo_credit_balance,
            $request->description,
            'bonus',
            null
        );

        return redirect()->route('wallet.index')
            ->with('success', '₦' . number_format($request->amount, 2) . ' promo credit added!');
    }

    /**
     * Display escrow transactions
     */
    public function escrow()
    {
        $user = auth()->user();

        $escrows = \App\Models\EscrowTransaction::with(['payer', 'payee', 'order'])
            ->where('payer_id', $user->id)
            ->orWhere('payee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalInEscrow = \App\Models\EscrowTransaction::where('payer_id', $user->id)
            ->whereIn('status', [\App\Models\EscrowTransaction::STATUS_PENDING, \App\Models\EscrowTransaction::STATUS_FUNDED])
            ->sum('total_amount');

        $totalReleased = \App\Models\EscrowTransaction::where('payee_id', $user->id)
            ->where('status', \App\Models\EscrowTransaction::STATUS_RELEASED)
            ->sum('amount');

        return view('escrow.index', [
            'escrows' => $escrows,
            'totalInEscrow' => $totalInEscrow,
            'totalReleased' => $totalReleased,
        ]);
    }
}
