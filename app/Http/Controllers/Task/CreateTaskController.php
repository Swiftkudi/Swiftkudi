<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\SaveTaskDraftRequest;
use App\Models\TaskCategory;
use App\Services\TaskCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * New Task Creation Controller.
 * Clean implementation with Service Layer pattern.
 * 
 * This controller replaces the old TaskController::create() and ::store() methods.
 */
class CreateTaskController extends Controller
{
    /**
     * @var TaskCreationService
     */
    private $taskCreationService;

    /**
     * Create a new controller instance.
     *
     * @param TaskCreationService $taskCreationService
     */
    public function __construct(TaskCreationService $taskCreationService)
    {
        $this->taskCreationService = $taskCreationService;
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the task creation form.
     * 
     * Step 1 of the task creation wizard.
     *
     * @param Request $request
     * @return View
     */
    public function showCreateForm(Request $request): View
    {
        $user = Auth::user();
        
        // Get categories grouped by type for the form
        $categoryConfig = $this->taskCreationService->getCategoryConfig();
        $categories = TaskCategory::getActiveCategories();

        // Check for prefill data from bundle session, then fall back to pending task data
        $prefillData = $this->getPrefillData($request) ?? session('task_creation_data');

        // Check for saved draft
        $draftData = $this->taskCreationService->getDraft($user);

        // Generate idempotency token for this session
        $idempotencyToken = $request->session()->get('task_idempotency_token');
        if (!$idempotencyToken) {
            $idempotencyToken = $this->taskCreationService->generateIdempotencyToken();
            $request->session()->put('task_idempotency_token', $idempotencyToken);
        }

        return view('tasks.create', compact(
            'categories',
            'categoryConfig',
            'prefillData',
            'draftData',
            'idempotencyToken'
        ));
    }

    /**
     * Store a new task.
     * 
     * Handles the final submission of the task creation form.
     *
     * @param CreateTaskRequest $request
     * @return JsonResponse
     */
    public function store(CreateTaskRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $idempotencyToken = $request->input('idempotency_token');

        // Validate permission
        if (!$user->canCreateTasks()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to create tasks at the moment.',
            ], 403);
        }

        // Create the task
        $result = $this->taskCreationService->createTask(
            $user,
            $request->validated(),
            $idempotencyToken,
            $request
        );

        // Clear draft if successful
        if ($result['success']) {
            $this->taskCreationService->clearDraft($user);
            session()->forget([
                'task_creation_data',
                'insufficient_balance_required',
                'deposit_success_redirect',
                'payment_success_redirect',
            ]);
        }

        $response = [
            'success' => $result['success'],
            'message' => $result['message'],
        ];

        if ($result['success'] && isset($result['task'])) {
            $response['task_id'] = $result['task']->id;
            $response['redirect'] = route('tasks.my-tasks');
        }

        if (isset($result['redirect'])) {
            $response['redirect'] = $result['redirect'];
        }

        // Include required amount if balance was insufficient
        if (isset($result['required_amount'])) {
            $response['required_amount'] = $result['required_amount'];
        }

        return response()->json($response, $result['status']);
    }

    /**
     * Save task draft (autosave).
     * 
     * AJAX endpoint for autosaving draft data.
     *
     * @param SaveTaskDraftRequest $request
     * @return JsonResponse
     */
    public function saveDraft(SaveTaskDraftRequest $request): JsonResponse
    {
        $user = Auth::user();

        $result = $this->taskCreationService->saveDraft($user, $request->validated());

        // Ensure deposit flow returns user to resume page with preserved data
        session([
            'deposit_success_redirect' => route('tasks.create.resume'),
            'task_creation_data' => $request->validated(),
        ]);

        if (!empty($request->input('budget'))) {
            session(['insufficient_balance_required' => (float) $request->input('budget')]);
        }

        return response()->json($result, $result['status']);
    }

    /**
     * Get saved draft data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDraft(Request $request): JsonResponse
    {
        $user = Auth::user();
        $draft = $this->taskCreationService->getDraft($user);

        return response()->json([
            'success' => true,
            'draft' => $draft,
        ]);
    }

    /**
     * Resume task creation after deposit.
     * 
     * Called after user deposits money to complete the task creation.
     * Pre-fills the form with the previously submitted data.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function resume(Request $request)
    {
        $user = Auth::user();
        
        // Get stored task creation data from session
        $storedData = session('task_creation_data') ?: $this->taskCreationService->getDraft($user);
        $requiredAmount = session('insufficient_balance_required');

        if (!$storedData) {
            return redirect()->route('tasks.create.new')
                ->with('error', 'No saved task form was found. Please fill the form again.');
        }

        // Keep resume redirect active until task is successfully submitted
        session(['deposit_success_redirect' => route('tasks.create.resume')]);

        if (!$requiredAmount && !empty($storedData['budget'])) {
            $requiredAmount = (float) $storedData['budget'];
            session(['insufficient_balance_required' => $requiredAmount]);
        }
        
        // Get categories for the form
        $categoryConfig = $this->taskCreationService->getCategoryConfig();
        $categories = TaskCategory::getActiveCategories();
        
        // Generate new idempotency token for this attempt
        $idempotencyToken = $this->taskCreationService->generateIdempotencyToken();
        $request->session()->put('task_idempotency_token', $idempotencyToken);
        
        // Check wallet balance to determine if user can proceed
        $wallet = $user->wallet;
        $canProceed = false;
        $message = '';
        $prefillData = $storedData;
        $draftData = null;
        
        if ($wallet && $requiredAmount) {
            $totalBalance = $wallet->withdrawable_balance + $wallet->promo_credit_balance;
            $canProceed = $totalBalance >= $requiredAmount;
            $message = $canProceed 
                ? 'Your deposit was successful! You can now submit your task.'
                : 'You still need more funds. Required: ₦' . number_format($requiredAmount, 2) . ', Available: ₦' . number_format($totalBalance, 2);
        }
        
        return view('tasks.create', compact(
            'categories',
            'categoryConfig',
            'prefillData',
            'draftData',
            'idempotencyToken'
        ))->with([
            'resumeMessage' => $message,
            'canProceed' => $canProceed,
        ]);
    }

    /**
     * Clear saved draft.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clearDraft(Request $request): JsonResponse
    {
        $user = Auth::user();
        $this->taskCreationService->clearDraft($user);

        return response()->json([
            'success' => true,
            'message' => 'Draft cleared successfully.',
        ]);
    }

    /**
     * Generate a new idempotency token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $newToken = $this->taskCreationService->generateIdempotencyToken();
        $request->session()->put('task_idempotency_token', $newToken);

        return response()->json([
            'success' => true,
            'token' => $newToken,
        ]);
    }

    /**
     * Validate task data without saving.
     * 
     * AJAX endpoint to validate form data in real-time.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateTaskData(Request $request): JsonResponse
    {
        // Run validation manually
        $createRequest = new CreateTaskRequest($request->all());
        $validator = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            $createRequest->rules()
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Validation passed.',
        ]);
    }

    /**
     * Calculate cost preview.
     * 
     * AJAX endpoint to calculate cost breakdown based on budget and quantity.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateCost(Request $request): JsonResponse
    {
        $budget = (float) $request->input('budget', 0);
        $quantity = (int) $request->input('quantity', 0);

        if ($budget <= 0 || $quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter valid budget and quantity.',
            ], 422);
        }

        // Calculate rewards (75% to worker, 25% platform fee)
        $workerReward = ($budget * 0.75) / $quantity;
        $platformFee = $budget * 0.25;
        $rewardPerTask = $budget / $quantity;

        return response()->json([
            'success' => true,
            'data' => [
                'budget' => $budget,
                'quantity' => $quantity,
                'reward_per_task' => round($workerReward, 2),
                'platform_fee' => round($platformFee, 2),
                'total_cost' => $budget,
                'per_task_total' => round($rewardPerTask, 2),
            ],
        ]);
    }

    /**
     * Get prefill data from session (e.g., from bundle selection).
     *
     * @param Request $request
     * @return array|null
     */
    private function getPrefillData(Request $request): ?array
    {
        $bundleData = $request->session()->get('bundle_data');

        if ($bundleData) {
            return [
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

        return null;
    }
}
