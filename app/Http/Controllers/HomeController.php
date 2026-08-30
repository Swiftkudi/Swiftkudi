<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\ProfessionalService;
use App\Models\ProfessionalServiceCategory;
use App\Models\ServiceProviderProfile;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $jobs = collect();
        $services = collect();
        $categories = collect();
        $freelancers = collect();

        try {
            $jobs = Job::query()
                ->active()
                ->with(['category', 'user'])
                ->latest()
                ->limit(6)
                ->get();
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $services = ProfessionalService::query()
                ->active()
                ->with(['category', 'seller'])
                ->latest()
                ->limit(6)
                ->get();
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $categories = ProfessionalServiceCategory::query()
                ->active()
                ->orderBy('name')
                ->limit(8)
                ->get();
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $freelancers = ServiceProviderProfile::query()
                ->with('user')
                ->whereHas('user')
                ->where('is_available', true)
                ->whereNotNull('slug')
                ->latest('updated_at')
                ->limit(4)
                ->get();
        } catch (\Throwable $e) {
            report($e);
        }

        return view('landing', compact('jobs', 'services', 'categories', 'freelancers'));
    }
}
