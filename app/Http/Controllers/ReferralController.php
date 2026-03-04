<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $referrals = Referral::where('user_id', $userId)
            ->where(function ($query) {
                $query->whereNotNull('referred_user_id')
                    ->orWhereNotNull('referred_email');
            })
            ->with('referredUser')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $baseStatsQuery = Referral::where('user_id', $userId)
            ->where(function ($query) {
                $query->whereNotNull('referred_user_id')
                    ->orWhereNotNull('referred_email');
            });

        $stats = [
            'total_referrals' => (clone $baseStatsQuery)->count(),
            'registered' => (clone $baseStatsQuery)->where('is_registered', true)->count(),
            'activated' => (clone $baseStatsQuery)->where('is_activated', true)->count(),
            'total_earned' => (clone $baseStatsQuery)->sum('reward_earned'),
        ];

        $authUser = User::find($userId);
        if ($authUser && empty($authUser->referral_code)) {
            $authUser->referral_code = User::generateReferralCode($authUser->name ?? $authUser->email ?? (string) $authUser->id);
            $authUser->save();
        }
        $referralCode = $authUser?->referral_code;
        $bonusAmount = SystemSetting::getReferralBonusAmount();

        return view('referrals.index', compact('referrals', 'stats', 'referralCode', 'bonusAmount'));
    }

    public function registerWithCode(Request $request)
    {
        // This would be used during registration
        session(['referral_code' => $request->code]);
        return response()->json(['success' => true]);
    }

    public function redirectWithCode($code)
    {
        // Accept a short referral link like /ref/{code} and store in session then redirect to registration
        session(['referral_code' => $code]);
        return redirect()->route('register');
    }

    public function checkReferral(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = trim((string) $request->code);

        $referrer = User::where('referral_code', $code)->first();

        if (!$referrer) {
            $legacyReferral = Referral::where('referral_code', $code)->first();
            if ($legacyReferral) {
                $referrer = $legacyReferral->user;
            }
        }

        if (!$referrer) {
            return response()->json(['success' => false, 'message' => 'Invalid referral code']);
        }

        if ($referrer->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Cannot refer yourself']);
        }

        $rewardAmount = SystemSetting::getReferralBonusAmount();

        return response()->json([
            'success' => true,
            'user' => $referrer->name,
            'reward' => '₦' . number_format($rewardAmount, 2),
        ]);
    }

    public function processReferralBonus($referralId)
    {
        $referral = Referral::findOrFail($referralId);

        if ($referral->is_activated || $referral->reward_earned > 0) {
            return back()->with('info', 'Bonus already processed');
        }

        DB::transaction(function () use ($referral) {
            // Use admin-configured referral bonus amount
            $bonus = SystemSetting::getReferralBonusAmount();

            $referrerWallet = $referral->user->wallet;
            if (!$referrerWallet) {
                Log::warning('Attempted to credit referral bonus but referrer has no wallet', ['referral_id' => $referral->id]);
                return;
            }

            // Credit the configured bonus to referrer's withdrawable balance
            $referrerWallet->addWithdrawable($bonus, 'referral_bonus', 'Referral bonus for ' . ($referral->referredUser->name ?? 'new user'));

            // Record transaction
            Transaction::create([
                'wallet_id' => $referrerWallet->id,
                'user_id' => $referral->user_id,
                'type' => Transaction::TYPE_REFERRAL_BONUS,
                'amount' => $bonus,
                'currency' => 'NGN',
                'status' => 'completed',
                'description' => 'Referral bonus for ' . ($referral->referredUser->name ?? 'new user'),
                'reference' => Transaction::generateReference('REF'),
            ]);

            // Update referral
            $referral->update(['reward_earned' => $bonus, 'is_activated' => true]);

            Log::info('Referral bonus processed', ['referral_id' => $referral->id, 'referrer_id' => $referral->user_id, 'amount' => $bonus]);
        });

        return back()->with('success', 'Referral bonus processed!');
    }
}
