<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Models\Wallet;
use App\Models\SystemSetting;
use App\Services\SwiftKudiService;
use App\Services\TaskGateProgressService;
use App\Notifications\EarningsUnlocked;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use App\Notifications\TaskAvailable;
use App\Notifications\TaskApproved;
use App\Notifications\TaskRejected;

class TaskController extends Controller
{
    protected $earnDeskService;
    protected $gateProgressService;

    public function __construct(SwiftKudiService $earnDeskService, TaskGateProgressService $gateProgressService)
    {
        $this->earnDeskService = $earnDeskService;
        $this->gateProgressService = $gateProgressService;
    }

    /**
     * Remove or null sensitive user fields for non-admin viewers so emails and personal data
     * are not exposed in templates. This is intentionally defensive: controllers should call
     * this before passing models that include `user` to non-admin views.
     */
    private function sanitizeUserForNonAdmin(User $user)
    {
        if (!$user) return;

        $sensitive = ['email', 'phone', 'phone_number', 'address', 'dob', 'date_of_birth', 'national_id', 'nin'];

        // Only null attributes that actually exist on the model to avoid notices
        $attrs = $user->getAttributes();
        foreach ($sensitive as $field) {
            if (array_key_exists($field, $attrs)) {
                $user->{$field} = null;
            }
        }
    }

    /**
     * Display available tasks (for workers)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Task::active()->where('user_id', '!=', $user->id);

        // Hide seeded/demo tasks in non-production environments when requested
        if (env('HIDE_SEED_TASKS', true)) {
            $query->where('is_sample', false);
        }

        // Filter by task type group (micro, ugc, referral, premium)
        if ($request->has('task_group') && $request->task_group) {
            $categoryIds = TaskCategory::where('task_type', $request->task_group)->pluck('id');
            $query->whereIn('category_id', $categoryIds);
        }

        // Filter by platform
        if ($request->has('platform') && $request->platform) {
            $query->where('platform', $request->platform);
        }

        // Filter by task type
        if ($request->has('task_type') && $request->task_type) {
            $query->where('task_type', $request->task_type);
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by reward range
        if ($request->has('min_reward') && $request->min_reward) {
            $query->where('worker_reward_per_task', '>=', $request->min_reward);
        }

        // Filter by featured
        if ($request->has('featured') && $request->featured) {
            $query->where('is_featured', true);
        }

        // Exclude completed tasks
        $completedTaskIds = TaskCompletion::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('task_id');
        
        $query->whereNotIn('id', $completedTaskIds);

        $tasks = $query->withCount(['completions as task_completions_count' => function ($completionQuery) {
                $completionQuery->where('status', TaskCompletion::STATUS_APPROVED);
            }])
            ->orderBy('is_featured', 'desc')
            ->orderBy('worker_reward_per_task', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = TaskCategory::getActiveCategories();
        
        // Get categories grouped by type
        $categoriesByGroup = [
            'micro' => TaskCategory::where('task_type', 'micro')->get(),
            'ugc' => TaskCategory::where('task_type', 'ugc')->get(),
            'referral' => TaskCategory::where('task_type', 'referral')->get(),
            'premium' => TaskCategory::where('task_type', 'premium')->get(),
        ];

        return view('tasks.index', compact('tasks', 'categories', 'categoriesByGroup'));
    }

    /**
     * Display task details
     */
    public function show(Task $task)
    {
        $user = Auth::user();

        // Check if user can perform this task
        $canPerform = $task->canUserPerform($user);

        // Check if user has already submitted
        $existingSubmission = TaskCompletion::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->first();

        return view('tasks.show', compact('task', 'canPerform', 'existingSubmission'));
    }

    /**
     * Display user's tasks (created and completed)
     */
    public function myTasks()
    {
        $user = Auth::user();

        // Tasks created by user
        // include a count of pending submissions per task for quick badges
        $createdTasks = Task::where('user_id', $user->id)
            ->withCount(['completions as pending_submissions_count' => function($q) {
                $q->where('status', 'pending');
            }])
            ->withCount(['completions as task_completions_count' => function($q) {
                $q->where('status', TaskCompletion::STATUS_APPROVED);
            }])
             ->orderBy('created_at', 'desc')
             ->paginate(10);

        // Tasks completed/submitted by user
        $completedTasks = TaskCompletion::where('user_id', $user->id)
            ->with('task')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Submissions received on tasks owned by this user (for review)
        $receivedSubmissions = TaskCompletion::whereHas('task', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['task', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'received_page');

        // Hide worker personal data for non-admins
        if (!($user->is_admin ?? false)) {
            foreach ($receivedSubmissions as $c) {
                if ($c->relationLoaded('user') && $c->user) {
                    $this->sanitizeUserForNonAdmin($c->user);
                }
            }
        }

        return view('tasks.my-tasks', compact('createdTasks', 'completedTasks', 'receivedSubmissions'));
    }

    /**
     * Show edit form for a task owner/admin.
     */
    public function edit(Task $task)
    {
        $user = Auth::user();
        $isAdmin = (bool) ($user->is_admin ?? false);

        if (!$isAdmin && (int) $task->user_id !== (int) $user->id) {
            abort(403, 'You are not authorized to edit this task.');
        }

        return view('tasks.edit', compact('task'));
    }

    /**
     * Show analytics for a task owner/admin.
     */
    public function analytics(Task $task)
    {
        $user = Auth::user();
        $isAdmin = (bool) ($user->is_admin ?? false);

        if (!$isAdmin && (int) $task->user_id !== (int) $user->id) {
            abort(403, 'You are not authorized to view analytics for this task.');
        }

        $completions = TaskCompletion::where('task_id', $task->id)
            ->with('user')
            ->latest()
            ->get();

        $stats = [
            'total_submissions' => $completions->count(),
            'pending' => $completions->where('status', TaskCompletion::STATUS_PENDING)->count(),
            'approved' => $completions->where('status', TaskCompletion::STATUS_APPROVED)->count(),
        ];

        return view('tasks.analytics', compact('task', 'completions', 'stats'));
    }

    /**
     * Update task editable fields.
     */
    public function update(Request $request, Task $task)
    {
        $user = Auth::user();
        $isAdmin = (bool) ($user->is_admin ?? false);

        if (!$isAdmin && (int) $task->user_id !== (int) $user->id) {
            abort(403, 'You are not authorized to update this task.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'proof_type' => 'required|string|in:' . implode(',', array_keys(Task::PROOF_TYPES)),
        ]);

        $task->update($validated);

        return redirect()->route('tasks.edit', $task)->with('success', 'Task updated successfully.');
    }

    /**
     * Pause an active task.
     */
    public function pause(Task $task)
    {
        $user = Auth::user();
        $isAdmin = (bool) ($user->is_admin ?? false);

        if (!$isAdmin && (int) $task->user_id !== (int) $user->id) {
            abort(403, 'You are not authorized to pause this task.');
        }

        $task->update(['is_active' => false]);

        return back()->with('success', 'Task paused successfully.');
    }

    /**
     * Resume a paused task.
     */
    public function resume(Task $task)
    {
        $user = Auth::user();
        $isAdmin = (bool) ($user->is_admin ?? false);

        if (!$isAdmin && (int) $task->user_id !== (int) $user->id) {
            abort(403, 'You are not authorized to resume this task.');
        }

        $task->update(['is_active' => true]);

        return back()->with('success', 'Task resumed successfully.');
    }

    /**
     * Delete a task.
     */
    public function destroy(Task $task)
    {
        $user = Auth::user();
        $isAdmin = (bool) ($user->is_admin ?? false);

        if (!$isAdmin && (int) $task->user_id !== (int) $user->id) {
            abort(403, 'You are not authorized to delete this task.');
        }

        $task->delete();

        return redirect()->route('tasks.my-tasks')->with('success', 'Task deleted successfully.');
    }

    /**
     * Review a specific submission for a task owner/admin.
     */
    public function submissionReview(TaskCompletion $completion)
    {
        $user = Auth::user();
        $task = $completion->task;

        if (!$task) {
            return redirect()->route('tasks.my-tasks')->with('error', 'Submission task was not found.');
        }

        $isAdmin = (bool) ($user->is_admin ?? false);
        if (!$isAdmin && (int) $task->user_id !== (int) $user->id) {
            abort(403, 'You are not authorized to review this submission.');
        }

        $completion->loadMissing('user');

        return view('tasks.submission-review', compact('completion', 'task'));
    }

    /**
     * Approve a submission from task owner/admin.
     */
    public function approve(Request $request, TaskCompletion $completion)
    {
        $user = Auth::user();
        $task = $completion->task;

        if (!$task) {
            return redirect()->route('tasks.my-tasks')->with('error', 'Submission task was not found.');
        }

        $isAdmin = (bool) ($user->is_admin ?? false);
        if (!$isAdmin && (int) $task->user_id !== (int) $user->id) {
            abort(403, 'You are not authorized to approve this submission.');
        }

        if (!$completion->isPending()) {
            return back()->with('error', 'This submission has already been reviewed.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $resolvedReward = (float) ($completion->reward_amount ?? 0);
        if ($resolvedReward <= 0) {
            $resolvedReward = (float) ($task->worker_reward_per_task ?? 0);
        }

        if ($resolvedReward <= 0 && Schema::hasColumn('tasks', 'reward_amount')) {
            $resolvedReward = (float) ($task->reward_amount ?? 0);
        }

        if ($resolvedReward <= 0 && Schema::hasColumn('tasks', 'reward_per_user')) {
            $resolvedReward = (float) ($task->reward_per_user ?? 0);
        }

        if ($resolvedReward <= 0) {
            return back()->with('error', 'This task has no valid worker reward configured.');
        }

        if (Schema::hasColumn('task_completions', 'reward_amount')) {
            $completion->reward_amount = $resolvedReward;
        }

        if ($request->filled('notes') && Schema::hasColumn('task_completions', 'admin_notes')) {
            $completion->admin_notes = $request->input('notes');
        }

        $completion->save();

        $result = $this->earnDeskService->awardTaskEarnings($completion->fresh(['task', 'user.wallet']));

        if (!($result['success'] ?? false)) {
            return back()->with('error', $result['message'] ?? 'Failed to approve submission.');
        }

        return back()->with('success', 'Submission approved and reward credited successfully.');
    }

    /**
     * Reject a submission from task owner/admin.
     */
    public function reject(Request $request, TaskCompletion $completion)
    {
        $user = Auth::user();
        $task = $completion->task;

        if (!$task) {
            return redirect()->route('tasks.my-tasks')->with('error', 'Submission task was not found.');
        }

        $isAdmin = (bool) ($user->is_admin ?? false);
        if (!$isAdmin && (int) $task->user_id !== (int) $user->id) {
            abort(403, 'You are not authorized to reject this submission.');
        }

        if (!$completion->isPending()) {
            return back()->with('error', 'This submission has already been reviewed.');
        }

        $request->validate([
            'reason' => 'required|string|in:' . implode(',', array_keys(TaskCompletion::REJECTION_REASONS)),
            'notes' => 'nullable|string|max:1000',
        ]);

        $completion->reject($request->input('reason'), $request->input('notes'));

        return back()->with('success', 'Submission rejected successfully.');
    }

    /**
     * Show create task form
     */
    public function create()
    {
        $categories = TaskCategory::getActiveCategories();

        // Check for bundle data from session (when user clicks "Use This Bundle" on start-journey page)
        $bundleData = session('bundle_data');
        $prefillData = null;

        if ($bundleData) {
            // Pre-fill with bundle data
            $prefillData = [
                'title' => $bundleData['name'] ?? 'New Campaign',
                'description' => $bundleData['description'] ?? 'Campaign created from bundle',
                'budget' => $bundleData['price'] ?? 2500,
                'quantity' => $bundleData['quantity'] ?? 1,
                'task_type' => $bundleData['task_type'] ?? null,
                'category_id' => $bundleData['category_id'] ?? null,
                'platform' => $bundleData['platform'] ?? null,
                'is_featured' => $bundleData['is_featured'] ?? false,
            ];
        }

        // Build a light-weight categoryConfig structure expected by the create view JS
        $categoryConfig = [];
        $groups = ['micro', 'ugc', 'referral', 'premium'];
        foreach ($groups as $g) {
            $groupCats = $categories->where('task_type', $g);
            if ($groupCats->isEmpty()) {
                // keep an empty group entry so the JS can rely on its existence if needed
                $categoryConfig[$g] = ['label' => $g, 'badge' => '', 'badgeText' => '', 'minBudget' => 0, 'platforms' => []];
                continue;
            }

            $platforms = [];
            foreach ($groupCats as $cat) {
                $pid = $cat->platform ?? 'other';
                if (!isset($platforms[$pid])) {
                    $platforms[$pid] = ['id' => $pid, 'name' => $pid, 'icon' => '', 'tasks' => []];
                }
                $platforms[$pid]['tasks'][] = [
                    'value' => $cat->id,
                    'name' => $cat->name,
                    'price' => [
                        'min' => (float) ($cat->min_price ?? $cat->base_price ?? 0),
                        'max' => (float) ($cat->max_price ?? $cat->base_price ?? 0),
                    ],
                    'categoryName' => $cat->name,
                    'proof_type' => $cat->proof_type ?? null,
                    'min_level' => (int) ($cat->min_level ?? 1),
                ];
            }

            $categoryConfig[$g] = ['label' => $g, 'badge' => '', 'badgeText' => '', 'minBudget' => 0, 'platforms' => array_values($platforms)];
        }

        return view('tasks.create', compact('categories', 'prefillData', 'categoryConfig'));
    }

    /**
     * Save an in-progress draft to session (AJAX)
     */
    public function saveDraft(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        // Minimal normalization
        $data['budget'] = isset($data['budget']) ? floatval($data['budget']) : 0;
        $data['quantity'] = isset($data['quantity']) ? intval($data['quantity']) : 0;
        $data['worker_reward_per_task'] = isset($data['worker_reward_per_task']) ? floatval($data['worker_reward_per_task']) : null;

        session()->put('pending_task_form', $data);
        session()->put('deposit_success_redirect', route('tasks.create.saved'));

        return response()->json(['success' => true]);
    }

    /**
     * Save a new task (and draft if applicable)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate incoming data
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'task_type' => 'required|string',
            'category_id' => 'required|exists:task_categories,id',
            'platform' => 'required|string',
            'is_featured' => 'boolean',
        ]);

        // Create the task
        $task = Task::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'budget' => $request->budget,
            'quantity' => $request->quantity,
            'task_type' => $request->task_type,
            'category_id' => $request->category_id,
            'platform' => $request->platform,
            'is_featured' => $request->is_featured ? true : false,
        ]);

        // Handle draft saving separately
        if ($request->has('save_draft') && $request->save_draft) {
            // Store draft data in session
            session()->put('pending_task_form', $request->all());
            session()->put('deposit_success_redirect', route('tasks.create.saved'));

            return response()->json(['success' => true, 'task_id' => $task->id]);
        }

        // Update user's task creation progress and check unlock status
        $this->gateProgressService->updateProgress($user, $request->budget);

        // Success notification
        Notification::send($user, new TaskApproved($task));

        return response()->json(['success' => true, 'task_id' => $task->id]);
    }

    /**
     * Mark a task as completed (worker action)
     */
    public function submit(Request $request, Task $task)
    {
        $user = Auth::user();

        $rules = [
            'proof_description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];

        if ($task->proof_type === 'link') {
            $rules['proof_data.link'] = 'required|url|max:2048';
        } else {
            $rules['proof_data.file'] = 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,webm|max:65536';
        }

        $validated = $request->validate($rules);

        if (!$task->canUserPerform($user)) {
            return back()->with('error', 'You cannot perform this task.');
        }

        $existingSubmission = TaskCompletion::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingSubmission) {
            return back()->with('error', 'You have already submitted this task.');
        }

        try {
            $proofData = [];
            $storedFilePath = null;

            if ($task->proof_type === 'link') {
                $proofData = [
                    'type' => 'link',
                    'link' => $validated['proof_data']['link'],
                ];
            } else {
                $file = $request->file('proof_data.file');
                $path = $file->store('task-proofs', 'public');
                $storedFilePath = $path;

                $proofData = [
                    'type' => 'file',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }

            $completionPayload = [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'status' => TaskCompletion::STATUS_PENDING,
                'proof_description' => $validated['proof_description'],
            ];

            if (Schema::hasColumn('task_completions', 'proof_data')) {
                $completionPayload['proof_data'] = json_encode($proofData);
            }

            if ($storedFilePath && Schema::hasColumn('task_completions', 'proof_screenshot')) {
                $completionPayload['proof_screenshot'] = $storedFilePath;
            }

            if (Schema::hasColumn('task_completions', 'worker_notes')) {
                $completionPayload['worker_notes'] = $validated['notes'] ?? null;
            }

            if (Schema::hasColumn('task_completions', 'admin_note') && !Schema::hasColumn('task_completions', 'worker_notes')) {
                $completionPayload['admin_note'] = $validated['notes'] ?? null;
            }

            if (Schema::hasColumn('task_completions', 'submitted_at')) {
                $completionPayload['submitted_at'] = now();
            }

            if (Schema::hasColumn('task_completions', 'ip_address')) {
                $completionPayload['ip_address'] = $request->ip();
            }

            if (Schema::hasColumn('task_completions', 'user_agent')) {
                $completionPayload['user_agent'] = substr((string) $request->userAgent(), 0, 1000);
            }

            TaskCompletion::create($completionPayload);

            return redirect()->route('tasks.show', $task)->with('success', 'Task submitted successfully. Awaiting review.');
        } catch (\Exception $e) {
            Log::error('Task submission failed', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Failed to submit task. Please try again.');
        }
    }

    /**
     * Mark a task as completed (worker action)
     */
    public function completeTask(Request $request, Task $task)
    {
        $user = Auth::user();

        // Validate request
        $request->validate([
            'submission_notes' => 'nullable|string',
        ]);

        // Check if task is already completed by this user
        $existingCompletion = TaskCompletion::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingCompletion) {
            return response()->json(['error' => 'Task already submitted.'], 400);
        }

        // Create task completion record
        $completion = TaskCompletion::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'submission_notes' => $request->submission_notes,
            'status' => 'pending',
        ]);

        // Success notification
        Notification::send($user, new EarningsUnlocked($completion));

        return response()->json(['success' => true, 'completion_id' => $completion->id]);
    }

    /**
     * Approve a task completion (admin action)
     */
    public function approveCompletion(Request $request, TaskCompletion $completion)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        // Update completion status
        $completion->status = 'approved';
        $completion->admin_notes = $request->notes;
        $completion->save();

        // Find the associated task
        $task = Task::find($completion->task_id);

        // Optionally, update task status or other logic
        // $task->status = 'completed';
        // $task->save();

        return response()->json(['success' => true]);
    }

    /**
     * Reject a task completion (admin action)
     */
    public function rejectCompletion(Request $request, TaskCompletion $completion)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        // Update completion status
        $completion->status = 'rejected';
        $completion->admin_notes = $request->notes;
        $completion->save();

        return response()->json(['success' => true]);
    }

    /**
     * Resend approval notification for a task completion (admin action)
     */
    public function resendApprovalNotification(TaskCompletion $completion)
    {
        // Find the user associated with the completion
        $user = User::find($completion->user_id);

        // Send notification
        Notification::send($user, new EarningsUnlocked($completion));

        return response()->json(['success' => true]);
    }
}
