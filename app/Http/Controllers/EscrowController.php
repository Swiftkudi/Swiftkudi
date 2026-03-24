<?php

namespace App\Http\Controllers;

use App\Models\EscrowTransaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EscrowController extends Controller
{
    /**
     * Display escrow overview.
     */
    public function index()
    {
        $user = Auth::user();

        $escrows = EscrowTransaction::with(['payer', 'payee', 'order'])
            ->where(function ($query) use ($user) {
                $query->where('payer_id', $user->id)
                    ->orWhere('payee_id', $user->id);
            })
            ->latest()
            ->paginate(15);

        $totalInEscrow = EscrowTransaction::where('payer_id', $user->id)
            ->whereIn('status', [EscrowTransaction::STATUS_PENDING, EscrowTransaction::STATUS_FUNDED])
            ->sum('total_amount');

        $totalReleased = EscrowTransaction::where('payee_id', $user->id)
            ->where('status', EscrowTransaction::STATUS_RELEASED)
            ->sum('amount');

        return view('escrow.index', compact('escrows', 'totalInEscrow', 'totalReleased'));
    }

    public function show(EscrowTransaction $escrow)
    {
        $userId = (int) Auth::id();
        if ($escrow->payer_id !== $userId && $escrow->payee_id !== $userId) {
            abort(403);
        }

        return view('escrow.show', compact('escrow'));
    }

    /**
     * Display active escrows.
     */
    public function active()
    {
        $user = Auth::user();

        $escrows = EscrowTransaction::with(['payer', 'payee', 'order'])
            ->where(function ($query) use ($user) {
                $query->where('payer_id', $user->id)
                    ->orWhere('payee_id', $user->id);
            })
            ->whereIn('status', [EscrowTransaction::STATUS_PENDING, EscrowTransaction::STATUS_FUNDED])
            ->latest()
            ->paginate(15);

        return view('escrow.index', compact('escrows'));
    }

    /**
     * Display released escrows.
     */
    public function released()
    {
        $user = Auth::user();

        $escrows = EscrowTransaction::with(['payer', 'payee', 'order'])
            ->where(function ($query) use ($user) {
                $query->where('payer_id', $user->id)
                    ->orWhere('payee_id', $user->id);
            })
            ->where('status', EscrowTransaction::STATUS_RELEASED)
            ->latest()
            ->paginate(15);

        return view('escrow.index', compact('escrows'));
    }

    /**
     * Display disputed escrows.
     */
    public function disputed()
    {
        $user = Auth::user();

        $escrows = EscrowTransaction::with(['payer', 'payee', 'order'])
            ->where(function ($query) use ($user) {
                $query->where('payer_id', $user->id)
                    ->orWhere('payee_id', $user->id);
            })
            ->where('status', EscrowTransaction::STATUS_DISPUTED)
            ->latest()
            ->paginate(15);

        return view('escrow.index', compact('escrows'));
    }

    /**
     * Release escrow.
     */
    public function release(EscrowTransaction $escrow)
    {
        $user = Auth::user();

        if ($escrow->payer_id !== $user->id) {
            abort(403);
        }

        if (!in_array($escrow->status, [EscrowTransaction::STATUS_PENDING, EscrowTransaction::STATUS_FUNDED], true)) {
            return back()->with('error', 'This escrow cannot be released.');
        }

        DB::transaction(function () use ($escrow) {
            $payeeWallet = Wallet::firstOrCreate(
                ['user_id' => $escrow->payee_id],
                [
                    'withdrawable_balance' => 0,
                    'promo_credit_balance' => 0,
                    'total_earned' => 0,
                    'total_spent' => 0,
                    'pending_balance' => 0,
                    'escrow_balance' => 0,
                ]
            );

            $payeeWallet->addWithdrawable((float) $escrow->amount, 'escrow_release');

            $escrow->status = EscrowTransaction::STATUS_RELEASED;
            $escrow->released_at = now();
            $escrow->save();
        });

        return back()->with('success', 'Payment released successfully!');
    }

    public function cancel(EscrowTransaction $escrow)
    {
        $user = Auth::user();

        if ($escrow->payer_id !== $user->id) {
            abort(403);
        }

        if (!in_array($escrow->status, [EscrowTransaction::STATUS_PENDING, EscrowTransaction::STATUS_FUNDED], true)) {
            return back()->with('error', 'This escrow cannot be cancelled.');
        }

        DB::transaction(function () use ($escrow) {
            $payerWallet = Wallet::firstOrCreate(
                ['user_id' => $escrow->payer_id],
                [
                    'withdrawable_balance' => 0,
                    'promo_credit_balance' => 0,
                    'total_earned' => 0,
                    'total_spent' => 0,
                    'pending_balance' => 0,
                    'escrow_balance' => 0,
                ]
            );

            $payerWallet->addWithdrawable((float) $escrow->total_amount, 'escrow_refund');

            $escrow->status = EscrowTransaction::STATUS_CANCELLED;
            $escrow->save();
        });

        return back()->with('success', 'Escrow cancelled and funds refunded.');
    }

    /**
     * Raise dispute.
     */
    public function dispute(Request $request, EscrowTransaction $escrow)
    {
        $user = Auth::user();

        if ($escrow->payer_id !== $user->id && $escrow->payee_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'reason' => 'required|string|min:20',
        ]);

        $escrow->status = EscrowTransaction::STATUS_DISPUTED;
        $escrow->save();

        return back()->with('success', 'Dispute raised. Our team will review and mediate.');
    }
}
