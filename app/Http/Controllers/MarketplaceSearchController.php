<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\ProfessionalService;
use App\Models\ServiceProviderProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceSearchController extends Controller
{
    /**
     * Unified public marketplace search backed by real SwiftKudi data.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'scope' => ['nullable', 'in:all,jobs,talent,services'],
        ]);

        $term = trim((string) ($validated['q'] ?? ''));
        $scope = $validated['scope'] ?? 'all';

        $jobs = collect();
        $talent = collect();
        $services = collect();
        $counts = ['jobs' => 0, 'talent' => 0, 'services' => 0];

        if ($term !== '') {
            $jobQuery = Job::query()
                ->active()
                ->with(['category', 'user'])
                ->where(function ($query) use ($term) {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('requirements', 'like', "%{$term}%")
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$term}%"));
                });

            $talentQuery = ServiceProviderProfile::query()
                ->available()
                ->with('user')
                ->whereHas('user')
                ->where(function ($query) use ($term) {
                    $query->where('professional_title', 'like', "%{$term}%")
                        ->orWhere('bio', 'like', "%{$term}%")
                        ->orWhere('skills', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$term}%"));
                });

            $serviceQuery = ProfessionalService::query()
                ->active()
                ->with(['category', 'seller'])
                ->where(function ($query) use ($term) {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$term}%"));
                });

            $counts = [
                'jobs' => (clone $jobQuery)->count(),
                'talent' => (clone $talentQuery)->count(),
                'services' => (clone $serviceQuery)->count(),
            ];

            if ($scope === 'all' || $scope === 'jobs') {
                $jobs = $jobQuery->latest()->limit($scope === 'all' ? 8 : 24)->get();
            }
            if ($scope === 'all' || $scope === 'talent') {
                $talent = $talentQuery->orderByDesc('average_rating')->orderByDesc('total_orders_completed')->limit($scope === 'all' ? 8 : 24)->get();
            }
            if ($scope === 'all' || $scope === 'services') {
                $services = $serviceQuery->orderByDesc('is_featured')->latest()->limit($scope === 'all' ? 8 : 24)->get();
            }
        }

        return view('marketplace.search', compact('term', 'scope', 'jobs', 'talent', 'services', 'counts'));
    }
}
