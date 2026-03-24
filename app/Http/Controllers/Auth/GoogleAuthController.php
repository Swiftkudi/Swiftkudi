<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirect()
    {
        // Check if Google OAuth is enabled
        if (!config('services.google.enabled', false)) {
            return redirect()->route('login')
                ->with('error', 'Google login is not available at this time.');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback()
    {
        try {
            // Check if Google OAuth is enabled
            if (!config('services.google.enabled', false)) {
                return redirect()->route('login')
                    ->with('error', 'Google login is not available at this time.');
            }

            $googleUser = Socialite::driver('google')->user();

            // Check if user already exists with this email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Update Google ID if not set
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                        'provider' => 'google',
                        'provider_id' => $googleUser->getId(),
                    ]);
                }

                if (empty($user->email_verified_at)) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(24)), // Random password for Google users
                    'email_verified_at' => now(), // Google accounts are pre-verified
                ]);

                // Create wallet for new user
                Wallet::firstOrCreate(
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

            // Log the user in
            Auth::login($user, true);

            // Check if user is admin
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Unable to login with Google. Please try again.');
        }
    }

    /**
     * Handle Google One Tap login
     */
    public function oneTap(Request $request)
    {
        // Check if Google OAuth is enabled
        if (!config('services.google.enabled', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Google login is not available at the moment.',
            ], 400);
        }

        $request->validate([
            'credential' => 'required|string',
        ]);

        try {
            // Decode the JWT token from Google
            $credential = $request->credential;
            $payload = $this->decodeGoogleJWT($credential);

            if (!$payload || !isset($payload['email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google sign-in failed. Please try again.',
                ], 400);
            }

            // Check if user already exists
            $user = User::where('email', $payload['email'])->first();

            if ($user) {
                // Update Google ID if not set
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $payload['sub'],
                        'avatar' => $payload['picture'] ?? null,
                        'provider' => 'google',
                        'provider_id' => $payload['sub'],
                    ]);
                }

                if (empty($user->email_verified_at)) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $payload['name'],
                    'email' => $payload['email'],
                    'google_id' => $payload['sub'],
                    'avatar' => $payload['picture'] ?? null,
                    'provider' => 'google',
                    'provider_id' => $payload['sub'],
                    'password' => bcrypt(Str::random(24)),
                    'email_verified_at' => now(),
                ]);

                // Create wallet
                Wallet::firstOrCreate(
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

            // Log the user in
            Auth::login($user, true);

            return response()->json([
                'success' => true,
                'redirect' => $user->role === 'admin' ? route('admin.dashboard') : route('dashboard'),
            ]);

        } catch (\Exception $e) {
            Log::error('Google One Tap Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to login with Google. Please try again.',
            ], 500);
        }
    }

    /**
     * Decode Google JWT token
     */
    private function decodeGoogleJWT($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = json_decode(base64_decode($parts[1]), true);
        return $payload;
    }
}
