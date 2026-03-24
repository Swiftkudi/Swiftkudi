<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Referral;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Prefill referral code from session or query param when present
        $referralCode = session('referral_code') ?? request('ref') ?? null;

        return view('auth.register', compact('referralCode'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => ['nullable', 'string', 'max:64'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Ensure the user has a friendly referral code (based on their name) for easy sharing
        if (empty($user->referral_code)) {
            $user->referral_code = User::generateReferralCode($user->name ?? $user->email);
            $user->save();
        }

        // If a referral code was provided (query/session or input), link it to this new user
        $appliedReferral = null;
        $incomingReferralCode = $request->filled('referral_code')
            ? trim((string) $request->referral_code)
            : trim((string) session('referral_code', ''));

        if ($incomingReferralCode !== '') {
            $referrer = User::where('referral_code', $incomingReferralCode)->first();

            if (!$referrer) {
                $legacyReferral = Referral::where('referral_code', $incomingReferralCode)->first();
                $referrer = $legacyReferral?->user;
            }

            if ($referrer && $referrer->id !== $user->id) {
                $ref = Referral::where('user_id', $referrer->id)
                    ->where(function ($query) use ($user) {
                        $query->where('referred_user_id', $user->id)
                            ->orWhere('referred_email', $user->email);
                    })
                    ->first();

                if (!$ref) {
                    $ref = new Referral();
                    $ref->user_id = $referrer->id;
                    $ref->referral_code = $incomingReferralCode;
                }

                $ref->referred_user_id = $user->id;
                $ref->referred_email = $user->email;
                $ref->is_registered = true;
                $ref->save();
                $appliedReferral = $ref;
            }

            // clear session-stored code after registration attempt
            session()->forget('referral_code');
        }

        // store who referred this user for traceability
        if ($appliedReferral) {
            $user->referred_by = $appliedReferral->user_id;
            $user->save();
            Log::info('User registered with referral', ['user_id' => $user->id, 'referrer_id' => $appliedReferral->user_id, 'referral_id' => $appliedReferral->id]);
        }

        event(new Registered($user));

        Auth::login($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account created successfully. Welcome to SwiftKudi!',
                'redirect' => RouteServiceProvider::HOME,
            ]);
        }

        return redirect(RouteServiceProvider::HOME);
    }
}
