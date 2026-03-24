<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\TaskNew;
use App\Models\TaskSubmissionNew;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show task marketplace - browse available tasks
     */
    public function index(Request $request): View
    {
        $category = $request->get('category');
        
        $query = TaskNew::active()
            ->where('status', TaskNew::STATUS_ACTIVE)
            ->whereRaw('workers_accepted_count < max_workers');

        if ($category) {
            $query->ofCategory($category);
        }

        $tasks = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = [
            'micro' => 'Micro Tasks',
            'ugc' => 'UGC Tasks',
            'growth' => 'Growth Tasks',
            'premium' => 'Premium Tasks',
        ];

        return view('tasks.index', compact('tasks', 'categories', 'category'));
    }

    /**
     * Show task creation form (Step 1)
     */
    public function create(): View
    {
        $settings = $this->taskService->getSettings();
        
        $categories = [
            'micro' => 'Micro Tasks',
            'ugc' => 'UGC Tasks',
            'growth' => 'Growth Tasks',
            'premium' => 'Premium Tasks',
        ];

        return view('tasks.new.create', compact('categories', 'settings'));
    }

    /**
     * Store new task (Step 1 - Create)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:micro,ugc,growth,premium',
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20|max:5000',
            'proof_instructions' => 'nullable|string|max:2000',
            'reward_per_user' => 'required|numeric|min:10',
            'max_workers' => 'required|integer|min:1|max:10000',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $user = Auth::user();
        if (!$user instanceof \App\Models\User) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        // Ensure user has wallet (activation is optional)
        if (!$user->wallet) {
            \App\Models\Wallet::firstOrCreate(
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
            $user->refresh();
        }

        $result = $this->taskService->createTask($user, $validated);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        // Store task ID in session for next steps
        session(['pending_task_id' => $result['task']->id]);

        return response()->json([
            'success' => true,
            'message' => 'Task created! Now fund it to make it active.',
            'task' => $result['task'],
            'required_funding' => $result['required_funding'],
            'next_step' => 'fund',
        ]);
    }

    /**
     * Fund task (Step 2 - Fund & Publish)
     */
    public function fund(Request $request)
    {
        $taskId = session('pending_task_id');
        
        if (!$taskId) {
            return response()->json([
                'success' => false,
                'message' => 'No pending task found',
            ], 400);
        }

        $task = TaskNew::find($taskId);
        
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        $user = Auth::user();
        $result = $this->taskService->fundTask($task, $user);

        // Clear session
        session()->forget('pending_task_id');

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'required' => $result['required'] ?? null,
                'available' => $result['available'] ?? null,
                'redirect' => $result['message'] === 'Insufficient funds to fund this task' 
                    ? route('wallet.deposit') 
                    : null,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'task' => $task,
            'funded_amount' => $result['funded_amount'],
        ]);
    }

    /**
     * Show my tasks (dashboard)
     */
    public function myTasks(): View
    {
        $user = Auth::user();
        
        $activeTasks = TaskNew::where('user_id', $user->id)
            ->whereIn('status', [TaskNew::STATUS_ACTIVE, TaskNew::STATUS_PAUSED, TaskNew::STATUS_PENDING_FUNDING])
            ->orderBy('created_at', 'desc')
            ->get();

        $completedTasks = TaskNew::where('user_id', $user->id)
            ->where('status', TaskNew::STATUS_COMPLETED)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $draftTasks = TaskNew::where('user_id', $user->id)
            ->where('status', TaskNew::STATUS_DRAFT)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tasks.new.my-tasks', compact('activeTasks', 'completedTasks', 'draftTasks'));
    }

    /**
     * Show task details
     */
    public function show(int $id): View
    {
        $task = TaskNew::with(['creator', 'submissions' => function($q) {
            $q->where('worker_id', Auth::id());
        }])->findOrFail($id);

        $userHasSubmitted = $task->submissions()->where('worker_id', Auth::id())->exists();

        return view('tasks.new.show', compact('task', 'userHasSubmitted'));
    }

    /**
     * Fund task - moves funds from wallet to escrow (Step 2)
     */
    public function fundTask(Request $request, int $task)
    {
        $task = TaskNew::findOrFail($task);
        $user = Auth::user();
        
        // Only task owner can fund
        if ($task->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $result = $this->taskService->fundTask($task, $user);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'required' => $result['required'] ?? null,
                'available' => $result['available'] ?? null,
                'redirect' => str_contains($result['message'], 'Insufficient') 
                    ? route('wallet.deposit') 
                    : null,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'task' => $task,
            'funded_amount' => $result['funded_amount'],
        ]);
    }

    /**
     * Work tasks - available tasks for workers to do
     */
    public function workTasks(Request $request): View
    {
        $category = $request->get('category');
        $search = $request->get('search');
        
        $query = TaskNew::active()
            ->where('status', TaskNew::STATUS_ACTIVE)
            ->whereRaw('workers_accepted_count < max_workers')
            ->where('user_id', '!=', Auth::id()); // Exclude own tasks

        if ($category) {
            $query->ofCategory($category);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = [
            'micro' => 'Micro Tasks',
            'ugc' => 'UGC Tasks',
            'growth' => 'Growth Tasks',
            'premium' => 'Premium Tasks',
        ];

        return view('tasks.new.work', compact('tasks', 'categories', 'category', 'search'));
    }

    /**
     * Submit work for a task
     */
    public function submitWork(Request $request, int $task)
    {
        $task = TaskNew::findOrFail($task);
        
        $validated = $request->validate([
            'proof_data' => 'required|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $result = $this->taskService->submitWork($task, $user, $validated['proof_data'], $validated['notes'] ?? null);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Submit work for a task (alias for submitWork)
     */
    public function submit(Request $request, int $task)
    {
        return $this->submitWork($request, $task);
    }

    /**
     * Show submissions for my task
     */
    public function submissions(int $id): View
    {
        $task = TaskNew::with(['submissions' => function($q) {
            $q->where('status', TaskSubmissionNew::STATUS_PENDING)
              ->with('worker');
        }])->findOrFail($id);

        // Verify ownership
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        $pendingSubmissions = $task->submissions()
            ->where('status', TaskSubmissionNew::STATUS_PENDING)
            ->with('worker')
            ->get();

        $approvedSubmissions = $task->submissions()
            ->where('status', TaskSubmissionNew::STATUS_APPROVED)
            ->with('worker')
            ->get();

        $rejectedSubmissions = $task->submissions()
            ->where('status', TaskSubmissionNew::STATUS_REJECTED)
            ->with('worker')
            ->get();

        return view('tasks.new.submissions', compact('task', 'pendingSubmissions', 'approvedSubmissions', 'rejectedSubmissions'));
    }

    /**
     * Approve a submission
     */
    public function approveSubmission(Request $request, int $submissionId)
    {
        $submission = TaskSubmissionNew::findOrFail($submissionId);
        $user = Auth::user();

        $result = $this->taskService->approveSubmission($submission, $user);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Reject a submission
     */
    public function rejectSubmission(Request $request, int $submissionId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $submission = TaskSubmissionNew::findOrFail($submissionId);
        $user = Auth::user();

        $result = $this->taskService->rejectSubmission($submission, $user, $validated['reason']);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Cancel a task
     */
    public function cancelTask(int $task)
    {
        $task = TaskNew::findOrFail($task);
        $user = Auth::user();

        $result = $this->taskService->cancelTask($task, $user);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Pause a task
     */
    public function pauseTask(int $task)
    {
        $task = TaskNew::findOrFail($task);
        $user = Auth::user();

        // Only task owner can pause
        if ($task->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $result = $this->taskService->pauseTask($task, $user);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Resume a paused task
     */
    public function resumeTask(int $task)
    {
        $task = TaskNew::findOrFail($task);
        $user = Auth::user();

        // Only task owner can resume
        if ($task->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $result = $this->taskService->resumeTask($task, $user);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get work opportunities (tasks user can do)
     */
    public function opportunities(Request $request): View
    {
        $user = Auth::user();
        
        // Get tasks user hasn't submitted to, excluding own tasks
        $tasks = TaskNew::active()
            ->where('user_id', '!=', $user->id)
            ->whereRaw('workers_accepted_count < max_workers')
            ->whereDoesntHave('submissions', function($q) use ($user) {
                $q->where('worker_id', $user->id);
            })
            ->orderBy('reward_per_user', 'desc')
            ->paginate(20);

        return view('tasks.opportunities', compact('tasks'));
    }
}
