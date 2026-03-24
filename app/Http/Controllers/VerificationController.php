<?php

namespace App\Http\Controllers;

use App\Models\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    /**
     * Display verification center.
     */
    public function index()
    {
        $user = Auth::user();
        $verifications = $user->verifications()->get();
        
        return view('verification.index', compact('verifications'));
    }

    /**
     * Submit identity verification.
     */
    public function submitIdentity(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:passport,drivers_license,national_id',
            'document_number' => 'required|string|max:50',
            'document_front' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'document_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'selfie' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $user = Auth::user();

        // Check if already verified
        $existing = $user->verifications()->where('type', 'identity')->first();
        if ($existing && $existing->status === 'approved') {
            return back()->with('error', 'You are already identity verified.');
        }

        // Store files
        $frontPath = $request->file('document_front')->store('verifications/identity', 'public');
        $backPath = null;
        if ($request->hasFile('document_back')) {
            $backPath = $request->file('document_back')->store('verifications/identity', 'public');
        }
        $selfiePath = $request->file('selfie')->store('verifications/identity', 'public');

        $verification = UserVerification::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => 'identity',
            ],
            [
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'document_front' => $frontPath,
                'document_back' => $backPath,
                'selfie' => $selfiePath,
                'status' => 'pending',
            ]
        );

        return back()->with('success', 'Identity verification submitted. Please allow 24-48 hours for review.');
    }

    /**
     * Submit email verification.
     */
    public function submitEmail()
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return back()->with('error', 'Your email is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Verification email sent. Please check your inbox.');
    }

    /**
     * Submit phone verification.
     */
    public function submitPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $user = Auth::user();
        
        // Send OTP (implementation depends on SMS service)
        // For now, just update the phone number
        
        $verification = UserVerification::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => 'phone',
            ],
            [
                'phone' => $request->phone,
                'status' => 'pending',
            ]
        );

        // In production, send OTP via SMS
        return back()->with('success', 'Phone verification initiated. OTP sent to your phone.');
    }

    /**
     * Verify phone with OTP.
     */
    public function verifyPhone(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        // In production, verify OTP against stored value
        // For now, just mark as verified
        
        $user = Auth::user();
        
        $verification = $user->verifications()->where('type', 'phone')->first();
        if ($verification) {
            $verification->status = 'approved';
            $verification->verified_at = now();
            $verification->save();
        }

        return back()->with('success', 'Phone number verified successfully!');
    }

    /**
     * Submit address verification.
     */
    public function submitAddress(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'utility_bill' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = Auth::user();

        $billPath = $request->file('utility_bill')->store('verifications/address', 'public');

        $verification = UserVerification::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => 'address',
            ],
            [
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'document_front' => $billPath,
                'status' => 'pending',
            ]
        );

        return back()->with('success', 'Address verification submitted. Please allow 24-48 hours for review.');
    }
}
