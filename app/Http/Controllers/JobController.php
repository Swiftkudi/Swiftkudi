<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\MarketplaceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    /**
     * Display a listing of jobs.
     */
    public function index(Request $request)
    {
        $query = Job::with(['user', 'category'])
            ->where('status', 'active')
            ->where('expires_at', '>', now());

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

        $jobs = $query->paginate(12);
        $categories = MarketplaceCategory::where('type', 'job')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('jobs.index', compact('jobs', 'categories'));
    }

    /**
     * Display the specified job.
     */
    public function show(Job $job)
    {
        $job->load(['user', 'category', 'applications']);

        // Check if user has applied
        $hasApplied = false;
        if (Auth::check()) {
            $hasApplied = JobApplication::where('job_id', $job->id)
                ->where('user_id', Auth::id())
                ->exists();
        }

        // Related jobs
        $relatedJobs = Job::with('user')
            ->where('id', '!=', $job->id)
            ->where('category_id', $job->category_id)
            ->where('status', 'active')
            ->limit(5)
            ->get();

        return view('jobs.show', compact('job', 'hasApplied', 'relatedJobs'));
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:marketplace_categories,id',
            'job_type' => 'required|in:full-time,part-time,contract,internship',
            'experience_level' => 'required|in:entry,intermediate,expert',
            'budget_min' => 'required|numeric|min:0',
            'budget_max' => 'required|numeric|gte:budget_min',
            'duration' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
        ]);

        $job = new Job($request->all());
        $job->user_id = Auth::id();
        $job->status = 'active';
        $job->expires_at = now()->addDays(30);
        $job->save();

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
        $applications = JobApplication::with(['job', 'job.user'])
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
            'cover_letter' => 'required|string',
            'proposal_amount' => 'required|numeric|min:0',
            'estimated_duration' => 'required|string|max:100',
        ]);

        // Check if already applied
        $existingApplication = JobApplication::where('job_id', $job->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingApplication) {
            return back()->with('error', 'You have already applied for this job.');
        }

        $application = new JobApplication([
            'cover_letter' => $request->cover_letter,
            'proposal_amount' => $request->proposal_amount,
            'estimated_duration' => $request->estimated_duration,
            'status' => 'pending',
        ]);

        $application->job_id = $job->id;
        $application->user_id = Auth::id();
        $application->save();

        return back()->with('success', 'Application submitted successfully!');
    }

    /**
     * Withdraw an application.
     */
    public function withdrawApplication(JobApplication $application)
    {
        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        $application->status = 'withdrawn';
        $application->save();

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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:marketplace_categories,id',
            'job_type' => 'required|in:full-time,part-time,contract,internship',
            'experience_level' => 'required|in:entry,intermediate,expert',
            'budget_min' => 'required|numeric|min:0',
            'budget_max' => 'required|numeric|gte:budget_min',
            'duration' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
        ]);

        $job->update($request->all());

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
    public function hireApplicant(JobApplication $application)
    {
        $job = $application->job;

        if ($job->user_id !== Auth::id()) {
            abort(403);
        }

        $application->status = 'hired';
        $application->save();

        // Reject other applications
        JobApplication::where('job_id', $job->id)
            ->where('id', '!=', $application->id)
            ->update(['status' => 'rejected']);

        return back()->with('success', 'Applicant hired successfully!');
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

        $application->status = 'rejected';
        $application->save();

        return back()->with('success', 'Application rejected.');
    }
}
