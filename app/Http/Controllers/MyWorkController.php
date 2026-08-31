<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractMilestone;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyWorkController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $userId = $user->id;

        $validated = $request->validate([
            'status' => ['nullable', 'in:active,completed,cancelled,disputed'],
        ]);

        $contractsQuery = Contract::query()
            ->with(['client', 'freelancer', 'job', 'milestones'])
            ->where(function ($query) use ($userId) {
                $query->where('client_id', $userId)->orWhere('freelancer_id', $userId);
            });

        if (!empty($validated['status'])) {
            $contractsQuery->where('status', $validated['status']);
        }

        $contracts = $contractsQuery->latest('updated_at')->limit(12)->get();

        $proposals = JobApplication::query()
            ->with(['job.user', 'contract'])
            ->where('user_id', $userId)
            ->latest()
            ->limit(8)
            ->get();

        $postedJobs = Job::query()
            ->with('category')
            ->withCount('applications')
            ->where('user_id', $userId)
            ->latest()
            ->limit(8)
            ->get();

        $submittedForReview = ContractMilestone::query()
            ->with('contract.freelancer')
            ->where('status', ContractMilestone::STATUS_SUBMITTED)
            ->whereHas('contract', fn ($query) => $query->where('client_id', $userId))
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        $revisionWork = ContractMilestone::query()
            ->with('contract.client')
            ->where('status', ContractMilestone::STATUS_REVISION_REQUESTED)
            ->whereHas('contract', fn ($query) => $query->where('freelancer_id', $userId))
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $stats = [
            'active_contracts' => Contract::where(function ($query) use ($userId) {
                $query->where('client_id', $userId)->orWhere('freelancer_id', $userId);
            })->where('status', Contract::STATUS_ACTIVE)->count(),
            'submitted_for_review' => ContractMilestone::where('status', ContractMilestone::STATUS_SUBMITTED)
                ->whereHas('contract', fn ($query) => $query->where('client_id', $userId))
                ->count(),
            'revision_requested' => ContractMilestone::where('status', ContractMilestone::STATUS_REVISION_REQUESTED)
                ->whereHas('contract', fn ($query) => $query->where('freelancer_id', $userId))
                ->count(),
            'pending_proposals' => JobApplication::where('user_id', $userId)
                ->whereIn('status', ['pending', 'reviewing', 'shortlisted'])
                ->count(),
            'proposals_to_review' => JobApplication::whereHas('job', fn ($query) => $query->where('user_id', $userId))
                ->whereIn('status', ['pending', 'reviewing', 'shortlisted'])
                ->count(),
        ];

        $hasClientWork = $postedJobs->isNotEmpty() || $stats['proposals_to_review'] > 0 || $stats['submitted_for_review'] > 0;
        $hasFreelancerWork = $proposals->isNotEmpty() || $stats['pending_proposals'] > 0 || $stats['revision_requested'] > 0;

        return view('my-work.index', compact(
            'contracts', 'proposals', 'postedJobs', 'submittedForReview', 'revisionWork',
            'stats', 'hasClientWork', 'hasFreelancerWork'
        ));
    }
}
