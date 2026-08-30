<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobBookmark;
use App\Models\MarketplaceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\NotificationManager;
use App\Services\ContractService;

class JobController extends Controller
{
    protected NotificationManager $notificationManager;

    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }
    /**
     * Display a listing of jobs.
     */
    public function index(Request $request)
    {
        $query = Job::with(['user', 'category'])
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

        // Add buyer category filter
        $user = auth()->user();
        if ($user && $user->account_type === 'buyer') {
            // Check if buyer onboarding is enabled and category selection is required
            if (\App\Services\OnboardingSettingsService::isBuyerOnboardingEnabled() &&
                \App\Services\OnboardingSettingsService::isBuyerCategorySelectionRequired() &&
                $user->buyer_onboarding_completed) {

                $buyerCategories = $user->getBuyerCategories();
                if (!empty($buyerCategories)) {
                    $query->whereIn('category_id', $buyerCategories);
                }
            }
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('job_type', $request->type);
        }

        // Filter by experience level
        if ($request->has('level') && $request->level) {
            $query->where('experience_level', $request->level);
        }

        // Budget filters
        if ($request->filled('budget_min')) {
            $query->where('budget_max', '>=', max(0, (float) $request->budget_min));
        }
        if ($request->filled('budget_max')) {
            $query->where('budget_min', '<=', max(0, (float) $request->budget_max));
        }

        // Saved jobs
        if ($request->boolean('saved') && Auth::check()) {
            $query->whereHas('bookmarks', fn ($q) => $q->where('user_id', Auth::id()));
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'budget_high':
                $query->orderByDesc('budget_max');
                break;
            case 'budget_low':
                $query->orderBy('budget_min');
                break;
            default:
                $query->latest();
        }

        $jobs = $query->paginate(12)->withQueryString();
        $categories = MarketplaceCategory::where('type', 'job')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $savedJobIds = Auth::check()
            ? JobBookmark::where('user_id', Auth::id())->pluck('job_id')->all()
            : [];

        return view('jobs.index', compact('jobs', 'categories', 'savedJobIds'));
    }

    /**
     * Display the specified job.
     */
    public function show(Job $job)
    {
        if ($job->status !== 'active' && (!Auth::check() || Auth::id() !== $job->user_id)) {
            abort(404);
        }

        if (Auth::id() !== $job->user_id) {
            $job->increment('views_count');
        }

        // Load relationships
        $job->load(['user.freelancerProfile', 'category', 'applications.user.freelancerProfile']);

        // Check if user has applied
        $hasApplied = false;
        $isSaved = false;
        if (Auth::check()) {
            $hasApplied = JobApplication::where('job_id', $job->id)
                ->where('user_id', Auth::id())
                ->where('status', '!=', 'withdrawn')
                ->exists();
            $isSaved = JobBookmark::where('job_id', $job->id)->where('user_id', Auth::id())->exists();
        }

        // Related jobs
        $relatedJobs = Job::with('user')
            ->where('id', '!=', $job->id)
            ->where('category_id', $job->category_id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->limit(5)
            ->get();

        $clientJobsPosted = Job::where('user_id', $job->user_id)->count();
        $clientHires = JobApplication::whereHas('job', fn ($q) => $q->where('user_id', $job->user_id))
            ->where('status', 'hired')
            ->count();

        return view('jobs.show', compact('job', 'hasApplied', 'isSaved', 'relatedJobs', 'clientJobsPosted', 'clientHires'));
    }

    /**
     * Show the form for creating a new job.
     */
    public function create()
    {
        $categories = MarketplaceCategory::where('type', 'job')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('jobs.create', compact('categories'));
    }

    /**
     * Store a newly created job.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:160',
            'description' => 'required|string|max:20000',
            'category_id' => 'required|exists:marketplace_categories,id',
            'job_type' => 'required|in:full-time,part-time,contract,internship',
            'experience_level' => 'required|in:entry,intermediate,expert',
            'budget_min' => 'required|numeric|min:0|max:1000000000',
            'budget_max' => 'required|numeric|gte:budget_min|max:1000000000',
            'duration' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'positions_available' => 'required|integer|min:1|max:50',
            'requirements' => 'nullable|string|max:10000',
            'benefits' => 'nullable|string|max:10000',
        ]);

        // Wrap in transaction for safety. Persist only validated marketplace fields.
        $payload = $request->only([
            'title', 'description', 'category_id', 'job_type', 'experience_level',
            'budget_min', 'budget_max', 'duration', 'location', 'positions_available',
        ]);
        $payload['requirements'] = $this->normalizeListInput($request->input('requirements'));
        $payload['benefits'] = $this->normalizeListInput($request->input('benefits'));

        $job = DB::transaction(function () use ($payload) {
            $job = new Job($payload);
            $job->user_id = Auth::id();
            $job->status = 'active';
            $job->expires_at = now()->addDays(30);
            $job->save();
            return $job;
        });

        // Notify job owner
        $this->notificationManager->notify(
            NotificationManager::EVENT_JOB_CREATED,
            $job->user,
            ['job_title' => $job->title]
        );

        return redirect()->route('jobs.show', $job)
            ->with('success', 'Job posted successfully!');
    }

    /**
     * Show the user's job posts.
     */
    public function myJobs()
    {
        $jobs = Job::with(['category', 'applications'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('jobs.my-jobs', compact('jobs'));
    }

    /**
     * Show the user's job applications.
     */
    public function applications()
    {
        $applications = JobApplication::with(['job', 'job.user', 'contract'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('jobs.applications', compact('applications'));
    }

    /**
     * Apply for a job.
     */
    public function apply(Request $request, Job $job)
    {
        $request->validate([
            'cover_letter' => 'required|string|max:5000',
            'proposal_amount' => 'required|numeric|min:0|max:1000000000',
            'estimated_duration' => 'required|string|max:100',
        ]);

        if ($job->user_id === Auth::id()) {
            return back()->with('error', 'You cannot submit a proposal to your own job.');
        }
        if ($job->status !== 'active' || ($job->expires_at && $job->expires_at->isPast())) {
            return back()->with('error', 'This job is no longer accepting proposals.');
        }
        if ($job->is_fully_hired) {
            return back()->with('error', 'All available positions for this job have been filled.');
        }

        // Check if already applied
        $existingApplication = JobApplication::where('job_id', $job->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingApplication) {
            return back()->with('error', 'You have already applied for this job.');
        }

        $application = DB::transaction(function () use ($request, $job) {
            $application = JobApplication::create([
                'job_id' => $job->id,
                'user_id' => Auth::id(),
                'cover_letter' => $request->cover_letter,
                'proposal_amount' => $request->proposal_amount,
                'estimated_duration' => $request->estimated_duration,
                'status' => 'pending',
            ]);
            $job->increment('applications_count');
            return $application;
        });

        // Notify job owner about new application
        $this->notificationManager->notify(
            NotificationManager::EVENT_JOB_APPLICATION_SUBMITTED,
            $job->user,
            [
                'application_id' => $application->id,
                'job_title' => $job->title,
                'applicant_name' => Auth::user()->name ?? 'A user',
                'recipient_role' => 'client',
                'action_url' => route('jobs.show', $job)
            ]
        );

        // Notify applicant about successful application submission
        $this->notificationManager->notify(
            NotificationManager::EVENT_JOB_APPLICATION_SUBMITTED,
            Auth::user(),
            [
                'application_id' => $application->id,
                'job_title' => $job->title,
                'recipient_role' => 'freelancer',
                'action_url' => route('jobs.show', $job)
            ]
        );

        return back()->with('success', 'Proposal submitted successfully.');
    }

    public function save(Job $job)
    {
        JobBookmark::firstOrCreate(['job_id' => $job->id, 'user_id' => Auth::id()]);
        return back()->with('success', 'Job saved.');
    }

    public function unsave(Job $job)
    {
        JobBookmark::where('job_id', $job->id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'Job removed from saved jobs.');
    }

    /**
     * Withdraw an application.
     */
    public function withdrawApplication(JobApplication $application)
    {
        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($application->status, ['pending', 'reviewing', 'shortlisted'], true)) {
            return back()->with('error', 'This proposal can no longer be withdrawn.');
        }

        $application->status = 'withdrawn';
        $application->save();
        $application->job()->where('applications_count', '>', 0)->decrement('applications_count');

        return back()->with('success', 'Application withdrawn successfully.');
    }

    /**
     * Edit a job post.
     */
    public function edit(Job $job)
    {
        if ($job->user_id !== Auth::id()) {
            abort(403);
        }

        $categories = MarketplaceCategory::where('type', 'job')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('jobs.edit', compact('job', 'categories'));
    }

    /**
     * Update a job post.
     */
    public function update(Request $request, Job $job)
    {
        if ($job->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:160',
            'description' => 'required|string|max:20000',
            'category_id' => 'required|exists:marketplace_categories,id',
            'job_type' => 'required|in:full-time,part-time,contract,internship',
            'experience_level' => 'required|in:entry,intermediate,expert',
            'budget_min' => 'required|numeric|min:0|max:1000000000',
            'budget_max' => 'required|numeric|gte:budget_min|max:1000000000',
            'duration' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'positions_available' => 'required|integer|min:1|max:50',
            'requirements' => 'nullable|string|max:10000',
            'benefits' => 'nullable|string|max:10000',
        ]);

        $payload = $request->only([
            'title', 'description', 'category_id', 'job_type', 'experience_level',
            'budget_min', 'budget_max', 'duration', 'location', 'positions_available',
        ]);
        $payload['requirements'] = $this->normalizeListInput($request->input('requirements'));
        $payload['benefits'] = $this->normalizeListInput($request->input('benefits'));
        $job->update($payload);

        return redirect()->route('jobs.show', $job)
            ->with('success', 'Job updated successfully!');
    }

    /**
     * Close a job post.
     */
    public function close(Job $job)
    {
        if ($job->user_id !== Auth::id()) {
            abort(403);
        }

        $job->status = 'closed';
        $job->save();

        // Notify job owner that job is closed
        $this->notificationManager->notify(
            NotificationManager::EVENT_JOB_CLOSED,
            $job->user,
            ['job_title' => $job->title]
        );

        return back()->with('success', 'Job closed successfully.');
    }

    /**
     * Delete a job post.
     */
    public function destroy(Job $job)
    {
        if ($job->user_id !== Auth::id()) {
            abort(403);
        }

        $job->delete();

        return redirect()->route('jobs.my-jobs')
            ->with('success', 'Job deleted successfully.');
    }

    /**
     * Hire an applicant (for job owner).
     */
    public function hireApplicant(Request $request, JobApplication $application)
    {
        $job = $application->job;

        if ($job->user_id !== Auth::id()) {
            abort(403);
        }

        if ($job->status !== 'active' || ($job->expires_at && $job->expires_at->isPast())) {
            return back()->with('error', 'This job is no longer open for hiring.');
        }

        if (!in_array($application->status, ['pending', 'reviewing', 'shortlisted'], true)) {
            return back()->with('error', 'This proposal can no longer be hired.');
        }

        if ($job->is_fully_hired) {
            return back()->with('error', 'All available positions for this job have already been filled.');
        }

        $contract = DB::transaction(function () use ($request, $application) {
            $lockedApplication = JobApplication::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $lockedJob = Job::whereKey($lockedApplication->job_id)->lockForUpdate()->firstOrFail();

            if ($lockedJob->user_id !== Auth::id()) {
                abort(403);
            }
            if ($lockedJob->status !== 'active' || ($lockedJob->expires_at && $lockedJob->expires_at->isPast())) {
                throw ValidationException::withMessages(['proposal' => 'This job is no longer open for hiring.']);
            }
            if (!in_array($lockedApplication->status, ['pending', 'reviewing', 'shortlisted'], true)) {
                throw ValidationException::withMessages(['proposal' => 'This proposal has already been processed.']);
            }

            $hiredCount = JobApplication::where('job_id', $lockedJob->id)->where('status', 'hired')->count();
            if ($hiredCount >= $lockedJob->positions_available) {
                throw ValidationException::withMessages(['proposal' => 'All available positions for this job have already been filled.']);
            }

            $lockedApplication->update([
                'status' => 'hired',
                'employer_notes' => $request->input('employer_notes'),
                'reviewed_at' => now(),
            ]);

            return app(ContractService::class)->createFromJobApplication($lockedApplication);
        });

        $application->refresh();
        $job->refresh();

        // Only reject remaining pending applications when all positions are filled
        $hiredCount = $job->hiredApplications()->count();
        if ($hiredCount >= $job->positions_available) {
            JobApplication::where('job_id', $job->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);
        }

        // Notify the hired applicant
        $this->notificationManager->notify(
            NotificationManager::EVENT_JOB_APPLICANT_HIRED,
            $application->user,
            [
                'application_id' => $application->id,
                'job_title' => $job->title,
                'recipient_role' => 'freelancer',
                'action_url' => route('contracts.show', $contract)
            ]
        );

        // Notify the job owner
        $this->notificationManager->notify(
            NotificationManager::EVENT_JOB_APPLICANT_HIRED,
            $job->user,
            [
                'application_id' => $application->id,
                'job_title' => $job->title,
                'applicant_name' => $application->user->name ?? 'An applicant',
                'recipient_role' => 'client',
                'action_url' => route('contracts.show', $contract)
            ]
        );

        $this->notificationManager->notify(NotificationManager::EVENT_CONTRACT_STARTED, $application->user, [
            'contract_title' => $contract->title,
            'action_url' => route('contracts.show', $contract),
        ]);

        return redirect()->route('contracts.show', $contract)->with('success', 'Applicant hired. The contract workroom is ready.');
    }

    /**
     * Reject an applicant.
     */
    public function rejectApplicant(JobApplication $application)
    {
        $job = $application->job;

        if ($job->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($application->status, ['pending', 'reviewing', 'shortlisted'], true)) {
            return back()->with('error', 'This proposal can no longer be declined.');
        }

        $application->status = 'rejected';
        $application->reviewed_at = now();
        $application->save();

        // Notify the rejected applicant
        $this->notificationManager->notify(
            NotificationManager::EVENT_JOB_APPLICANT_REJECTED,
            $application->user,
            [
                'application_id' => $application->id,
                'job_title' => $job->title,
                'reason' => 'The client has chosen to move forward with other candidates.',
                'recipient_role' => 'freelancer',
                'action_url' => route('jobs.show', $job),
            ]
        );

        // Notify the job owner
        $this->notificationManager->notify(
            NotificationManager::EVENT_JOB_APPLICANT_REJECTED,
            $job->user,
            [
                'application_id' => $application->id,
                'job_title' => $job->title,
                'applicant_name' => $application->user->name ?? 'An applicant',
                'recipient_role' => 'client',
                'action_url' => route('jobs.show', $job),
            ]
        );

        return back()->with('success', 'Application rejected.');
    }

    private function normalizeListInput(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        return collect(preg_split('/[\r\n]+/', $value) ?: [])
            ->map(fn ($item) => trim((string) $item, " \t\n\r\0\x0B-•"))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

}
