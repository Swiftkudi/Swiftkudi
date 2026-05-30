<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalServiceCategory;
use App\Models\University;
use App\Models\MarketplaceCategory;
use App\Services\OnboardingSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceOnboardingController extends Controller
{
    public function buyer()
    {
        $user = Auth::user();

        if ($user->account_type !== 'buyer') {
            return redirect()->route('dashboard')->with('info', 'Buyer onboarding is only available for buyer accounts.');
        }

        if (!OnboardingSettingsService::isBuyerOnboardingEnabled()) {
            return redirect()->route('dashboard')->with('info', 'Buyer onboarding is currently disabled.');
        }

        $professionalCategories = ProfessionalServiceCategory::where('is_active', true)->get();
        $digitalCategories = MarketplaceCategory::where('type', 'digital_product')
            ->where('is_active', true)
            ->get();
        $growthCategories = MarketplaceCategory::where('type', 'growth')
            ->where('is_active', true)
            ->get();
        $jobCategories = MarketplaceCategory::where('type', 'job')
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->get();

        $selectedCategories = $user->getBuyerCategories();
        $categoryLimits = OnboardingSettingsService::getBuyerCategoryLimits();
        $universities = University::active()->orderBy('name')->get();
        $contactPreferences = array_merge([
            'email' => true,
            'chat' => true,
            'sms' => false,
        ], $user->marketplace_contact_preferences ?? []);

        return view('marketplace.onboarding.buyer', compact(
            'professionalCategories',
            'digitalCategories',
            'growthCategories',
            'jobCategories',
            'selectedCategories',
            'categoryLimits',
            'universities',
            'contactPreferences'
        ));
    }

    public function storeBuyer(Request $request)
    {
        $user = Auth::user();

        if ($user->account_type !== 'buyer') {
            return redirect()->route('dashboard')->with('info', 'Buyer onboarding is only available for buyer accounts.');
        }

        if (!OnboardingSettingsService::isBuyerOnboardingEnabled()) {
            return redirect()->route('dashboard')->with('info', 'Buyer onboarding is currently disabled.');
        }

        $limits = OnboardingSettingsService::getBuyerCategoryLimits();
        $minCategories = $limits['min'];
        $maxCategories = $limits['max'];

        $professionalIds = ProfessionalServiceCategory::where('is_active', true)->pluck('id')->toArray();
        $digitalIds = MarketplaceCategory::where('type', 'digital_product')->where('is_active', true)->pluck('id')->toArray();
        $growthIds = MarketplaceCategory::where('type', 'growth')->where('is_active', true)->pluck('id')->toArray();
        $jobIds = MarketplaceCategory::where('type', 'job')->where('is_active', true)->pluck('id')->toArray();

        $validIds = array_merge($professionalIds, $digitalIds, $growthIds, $jobIds);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'university_id' => 'nullable|integer|exists:universities,id',
            'campus' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'year_of_study' => 'nullable|string|max:100',
            'marketplace_bio' => 'nullable|string|max:2000',
            'marketplace_contact_preferences' => 'nullable|array',
            'marketplace_contact_preferences.email' => 'nullable|boolean',
            'marketplace_contact_preferences.chat' => 'nullable|boolean',
            'marketplace_contact_preferences.sms' => 'nullable|boolean',
            'marketplace_avatar' => 'nullable|image|max:4096',
            'categories' => "required|array|min:{$minCategories}|max:{$maxCategories}",
            'categories.*' => 'integer',
        ], [
            'name.required' => 'Please enter your full name.',
            'categories.required' => 'Please select at least one category.',
            'categories.min' => "Please select at least {$minCategories} category(ies).",
            'categories.max' => "You can select a maximum of {$maxCategories} categories.",
        ]);

        foreach ($validated['categories'] as $categoryId) {
            if (!in_array($categoryId, $validIds, true)) {
                return back()->withErrors(['categories' => 'Invalid category selected.']);
            }
        }

        if ($request->hasFile('marketplace_avatar')) {
            $validated['marketplace_avatar'] = $request->file('marketplace_avatar')->store('marketplace/avatars', 'public');
        }

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? $user->phone,
            'university_id' => $validated['university_id'] ?? $user->university_id,
            'campus' => $validated['campus'] ?? $user->campus,
            'faculty' => $validated['faculty'] ?? $user->faculty,
            'year_of_study' => $validated['year_of_study'] ?? $user->year_of_study,
            'marketplace_bio' => $validated['marketplace_bio'] ?? $user->marketplace_bio,
            'marketplace_contact_preferences' => array_filter($validated['marketplace_contact_preferences'] ?? [], fn($value) => $value !== null),
            'marketplace_avatar' => $validated['marketplace_avatar'] ?? $user->marketplace_avatar,
        ]);

        $user->setBuyerCategories($validated['categories']);
        $user->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
        ]);

        return redirect()->route('marketplace.listings.index')
            ->with('success', 'Student buyer profile saved and your marketplace is now personalized.');
    }

    public function seller()
    {
        $user = Auth::user();

        if (!in_array($user->account_type, ['digital_seller', 'growth_seller', 'freelancer'], true)) {
            return redirect()->route('dashboard')->with('info', 'Seller onboarding is only available for student marketplace sellers.');
        }

        $universities = University::active()->orderBy('name')->get();
        $contactPreferences = array_merge([
            'email' => true,
            'chat' => true,
            'sms' => false,
        ], $user->marketplace_contact_preferences ?? []);

        return view('marketplace.onboarding.seller', compact('universities', 'contactPreferences'));
    }

    public function storeSeller(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->account_type, ['digital_seller', 'growth_seller', 'freelancer'], true)) {
            return redirect()->route('dashboard')->with('info', 'Seller onboarding is only available for student marketplace sellers.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'university_id' => 'nullable|integer|exists:universities,id',
            'campus' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'year_of_study' => 'nullable|string|max:100',
            'marketplace_bio' => 'nullable|string|max:2000',
            'marketplace_contact_preferences' => 'nullable|array',
            'marketplace_contact_preferences.email' => 'nullable|boolean',
            'marketplace_contact_preferences.chat' => 'nullable|boolean',
            'marketplace_contact_preferences.sms' => 'nullable|boolean',
            'marketplace_avatar' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('marketplace_avatar')) {
            $validated['marketplace_avatar'] = $request->file('marketplace_avatar')->store('marketplace/avatars', 'public');
        }

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? $user->phone,
            'university_id' => $validated['university_id'] ?? $user->university_id,
            'campus' => $validated['campus'] ?? $user->campus,
            'faculty' => $validated['faculty'] ?? $user->faculty,
            'year_of_study' => $validated['year_of_study'] ?? $user->year_of_study,
            'marketplace_bio' => $validated['marketplace_bio'] ?? $user->marketplace_bio,
            'marketplace_contact_preferences' => array_filter($validated['marketplace_contact_preferences'] ?? [], fn($value) => $value !== null),
            'marketplace_avatar' => $validated['marketplace_avatar'] ?? $user->marketplace_avatar,
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
        ]);

        return redirect()->route('marketplace.seller.dashboard')
            ->with('success', 'Student seller profile saved! You can now begin listing on the campus marketplace.');
    }
}
