<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct()
    {
        $this->subscriptionService = app(SubscriptionService::class);
    }

    public function index()
    {
        $user = Auth::user();
        $isPremium = $this->subscriptionService->isPremium($user);
        $price = $this->subscriptionService->getPrice();
        $activeSub = \App\Models\UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('metadata->marketplace_plan', true)
            ->first();

        return view('marketplace.subscription.index', compact('isPremium', 'price', 'activeSub'));
    }

    public function subscribe(Request $request)
    {
        $result = $this->subscriptionService->subscribe(Auth::user());

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('marketplace.subscription.index')
            ->with('success', $result['message']);
    }

    public function cancel(Request $request)
    {
        $result = $this->subscriptionService->cancel(Auth::user());

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('marketplace.subscription.index')
            ->with('success', $result['message']);
    }

    public function processWebhook(Request $request)
    {
        // Handle payment gateway webhook for subscription payments
        // Validates signature and processes subscription payment confirmation
        $payload = $request->all();

        // Verify webhook signature (placeholder - implement with your gateway)
        // $isValid = $this->verifyWebhookSignature($request);
        // if (!$isValid) { return response()->json(['error' => 'Invalid signature'], 401); }

        if (isset($payload['event']) && $payload['event'] === 'subscription.payment.success') {
            $subscription = \App\Models\UserSubscription::where('reference', $payload['data']['reference'] ?? '')->first();

            if ($subscription && $subscription->status === 'pending') {
                $subscription->update([
                    'status' => 'active',
                    'payment_method' => $payload['data']['gateway'] ?? 'paystack',
                    'transaction_id' => $payload['data']['transaction_id'] ?? null,
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}