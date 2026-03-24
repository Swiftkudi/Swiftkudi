<?php

namespace App\Services;

use App\Jobs\ProcessTaskCreation;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskCreationLog;
use App\Models\User;
use App\Notifications\TaskApproved;
use App\Repositories\TaskRepository;
use App\Services\TaskGateProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service Layer for Task Creation operations.
 * Handles all business logic for creating tasks with idempotency, 
 * validation, and transaction safety.
 */
class TaskCreationService
{
    /**
     * @var TaskRepository
     */
    private $taskRepository;

    /**
     * @var SwiftKudiService
     */
    private $earnDeskService;

    /**
     * @var TaskGateProgressService
     */
    private $gateProgressService;

    /**
     * Create a new service instance.
     *
     * @param TaskRepository $taskRepository
     * @param SwiftKudiService $earnDeskService
     * @param TaskGateProgressService $gateProgressService
     */
    public function __construct(
        TaskRepository $taskRepository,
        SwiftKudiService $earnDeskService,
        TaskGateProgressService $gateProgressService
    ) {
        $this->taskRepository = $taskRepository;
        $this->earnDeskService = $earnDeskService;
        $this->gateProgressService = $gateProgressService;
    }

    /**
     * Create a new task with full validation and idempotency handling.
     *
     * @param User $user
     * @param array $data
     * @param string $idempotencyToken
     * @param Request $request
     * @return array{task: Task|null, success: bool, message: string, status: int}
     */
    public function createTask(
        User $user,
        array $data,
        string $idempotencyToken,
        Request $request
    ): array {
        // Check for existing pending request with same token (idempotency)
        $existingLog = $this->checkIdempotency($idempotencyToken, $user->id);
        
        if ($existingLog) {
            if ($existingLog->status === TaskCreationLog::STATUS_COMPLETED) {
                // Return existing successful result
                return [
                    'task' => $existingLog->task,
                    'success' => true,
                    'message' => 'Your last task submission was already processed successfully.',
                    'status' => Response::HTTP_CREATED,
                    'existing' => true,
                ];
            }

            if ($existingLog->status === TaskCreationLog::STATUS_PROCESSING) {
                // Request is still being processed
                return [
                    'task' => null,
                    'success' => false,
                    'message' => 'Your previous request is still being processed. Please wait.',
                    'status' => Response::HTTP_CONFLICT,
                ];
            }

            if ($existingLog->status === TaskCreationLog::STATUS_FAILED) {
                // Allow retry - generate a new token for the new attempt
                $idempotencyToken = (string) Str::uuid();
            }
        }

        // Create new creation log with the (possibly new) token
        $creationLog = $this->createCreationLog(
            $user->id,
            $idempotencyToken,
            $data,
            $request
        );

        try {
            // Ensure user wallet exists (activation is optional)
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

            // Validate user can create tasks
            if (!$user->canCreateTasks()) {
                $this->logFailure($creationLog, 'User is not authorized to create tasks');
                return [
                    'task' => null,
                    'success' => false,
                    'message' => 'You are not authorized to create tasks at the moment.',
                    'status' => Response::HTTP_FORBIDDEN,
                ];
            }

            // Check rate limiting
            if (!$this->checkRateLimit($user->id)) {
                $this->logFailure($creationLog, 'Rate limit exceeded');
                return [
                    'task' => null,
                    'success' => false,
                    'message' => 'Too many tasks created. Please try again later.',
                    'status' => Response::HTTP_TOO_MANY_REQUESTS,
                ];
            }

            // Check wallet balance if not saving as draft
            $isDraft = $data['save_draft'] ?? false;
            
            if (!$isDraft) {
                $walletCheck = $this->checkWalletBalance($user, $data['budget']);
                if (!$walletCheck['sufficient']) {
                    $this->logFailure($creationLog, 'Insufficient wallet balance');
                    
                    // Store form data and required amount in session for redirect after deposit
                    $requiredAmount = $data['budget'] - $walletCheck['balance'];
                    session([
                        'insufficient_balance_required' => $data['budget'],
                        'task_creation_data' => $data,
                        'deposit_success_redirect' => route('tasks.create.resume'),
                    ]);
                    
                    return [
                        'task' => null,
                        'success' => false,
                        'message' => $walletCheck['message'],
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        'redirect' => route('wallet.deposit'),
                        'required_amount' => $requiredAmount,
                    ];
                }
            }

            // Validate category exists and is active
            $category = $this->taskRepository->getCategoryById($data['category_id']);
            if (!$category || !$category->is_active) {
                $this->logFailure($creationLog, 'Invalid or inactive category');
                return [
                    'task' => null,
                    'success' => false,
                    'message' => 'The selected category is not available.',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                ];
            }

            // Create the task using repository (transaction-safe)
            // Ensure required foreign key is always present.
            $resolvedPlatform = !empty($data['platform']) ? $data['platform'] : ($category->platform ?? null);
            if (empty($resolvedPlatform)) {
                $this->logFailure($creationLog, 'Unable to resolve platform from selected category');
                return [
                    'task' => null,
                    'success' => false,
                    'message' => 'We could not determine the platform for the selected task type. Please reselect the task category and try again.',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                ];
            }

            $taskPayload = array_merge($data, [
                'user_id' => $user->id,
                'platform' => $resolvedPlatform,
            ]);
            $task = $this->taskRepository->create($taskPayload);

            // Deduct from wallet if not a draft
            if (!$isDraft) {
                $this->deductFromWallet($user, $data['budget'], $task);
            }

            // Mark creation log as completed
            $creationLog->markCompleted($task, [
                'task_id' => $task->id,
                'budget' => $task->budget,
                'quantity' => $task->quantity,
            ]);

            // Update user's task creation progress and check unlock status
            if (!$isDraft && isset($data['budget']) && $data['budget'] > 0) {
                $this->gateProgressService->updateProgress($user, $data['budget']);
            }

            // Send notification
            $this->sendTaskCreatedNotification($user, $task);

            Log::info('Task created successfully', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'budget' => $task->budget,
                'category' => $category->name,
            ]);

            return [
                'task' => $task,
                'success' => true,
                'message' => $isDraft ? 'Task saved as draft.' : 'Task created successfully!',
                'status' => Response::HTTP_CREATED,
            ];

        } catch (\Exception $e) {
            Log::error('Task creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->logFailure($creationLog, $e->getMessage());

            $errorMessage = $this->mapTaskCreationExceptionMessage($e->getMessage());

            return [
                'task' => null,
                'success' => false,
                'message' => $errorMessage,
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Convert low-level exception text to clear user-facing messages.
     */
    protected function mapTaskCreationExceptionMessage(string $exceptionMessage): string
    {
        $message = strtolower($exceptionMessage);

        if (str_contains($message, 'platform') && (str_contains($message, 'null') || str_contains($message, 'cannot be'))) {
            return 'Please select a valid task type/category so we can detect the platform, then submit again.';
        }

        if (str_contains($message, 'category') && (str_contains($message, 'null') || str_contains($message, 'foreign key') || str_contains($message, 'constraint'))) {
            return 'The selected task category is invalid or missing. Please reselect the task category and try again.';
        }

        if (str_contains($message, 'duplicate') || str_contains($message, 'unique')) {
            return 'Your last submission was already received. Please refresh the page and submit again.';
        }

        return 'We could not create your task right now. Please review your details and try again.';
    }

    /**
     * Queue a task creation for async processing (for heavy operations).
     *
     * @param User $user
     * @param array $data
     * @param string $idempotencyToken
     * @param Request $request
     * @return array
     */
    public function queueTaskCreation(
        User $user,
        array $data,
        string $idempotencyToken,
        Request $request
    ): array {
        // Create creation log
        $creationLog = $this->createCreationLog(
            $user->id,
            $idempotencyToken,
            $data,
            $request
        );

        // Dispatch job to queue
        ProcessTaskCreation::dispatch(
            $user,
            $data,
            $idempotencyToken
        )->onQueue('task-creation');

        return [
            'success' => true,
            'message' => 'Task creation is being processed. You will be notified when complete.',
            'status' => Response::HTTP_ACCEPTED,
            'token' => $idempotencyToken,
        ];
    }

    /**
     * Save task draft to session.
     *
     * @param User $user
     * @param array $data
     * @return array
     */
    public function saveDraft(User $user, array $data): array
    {
        try {
            // Store draft in session with user-specific key
            $draftKey = "task_draft_{$user->id}";
            
            // Sanitize data before storing
            $sanitizedData = $this->sanitizeDraftData($data);
            
            // Store in session (expires in 24 hours)
            session()->put($draftKey, $sanitizedData);
            session()->put("{$draftKey}_expires", now()->addHours(24));

            return [
                'success' => true,
                'message' => 'Draft saved successfully.',
                'status' => Response::HTTP_OK,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to save task draft', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to save draft. Please try again.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Retrieve saved draft for user.
     *
     * @param User $user
     * @return array|null
     */
    public function getDraft(User $user): ?array
    {
        $draftKey = "task_draft_{$user->id}";
        $expiresAt = session("{$draftKey}_expires");

        if (!$expiresAt || now()->isAfter($expiresAt)) {
            // Draft expired, clear it
            session()->forget($draftKey);
            session()->forget("{$draftKey}_expires");
            return null;
        }

        return session()->get($draftKey);
    }

    /**
     * Clear saved draft for user.
     *
     * @param User $user
     * @return void
     */
    public function clearDraft(User $user): void
    {
        $draftKey = "task_draft_{$user->id}";
        session()->forget($draftKey);
        session()->forget("{$draftKey}_expires");
    }

    /**
     * Generate a new idempotency token.
     *
     * @return string
     */
    public function generateIdempotencyToken(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Check for existing request with same idempotency token.
     *
     * @param string $token
     * @param int $userId
     * @return TaskCreationLog|null
     */
    protected function checkIdempotency(string $token, int $userId): ?TaskCreationLog
    {
        return TaskCreationLog::where('token', $token)
            ->where('user_id', $userId)
            ->whereIn('status', [
                TaskCreationLog::STATUS_PENDING,
                TaskCreationLog::STATUS_PROCESSING,
                TaskCreationLog::STATUS_COMPLETED,
                TaskCreationLog::STATUS_FAILED, // Include FAILED to allow retries after failures
            ])
            ->first();
    }

    /**
     * Create a new task creation log entry.
     *
     * @param int $userId
     * @param string $token
     * @param array $data
     * @param Request $request
     * @return TaskCreationLog
     */
    protected function createCreationLog(
        int $userId,
        string $token,
        array $data,
        Request $request
    ): TaskCreationLog {
        return TaskCreationLog::create([
            'token' => $token,
            'user_id' => $userId,
            'status' => TaskCreationLog::STATUS_PROCESSING,
            'request_payload' => $this->sanitizeForLogging($data),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Log a failure for a creation log entry.
     *
     * @param TaskCreationLog $log
     * @param string $reason
     * @return void
     */
    protected function logFailure(TaskCreationLog $log, string $reason): void
    {
        $log->markFailed($reason);
    }

    /**
     * Check rate limiting for task creation.
     *
     * @param int $userId
     * @return bool
     */
    protected function checkRateLimit(int $userId): bool
    {
        // Get rate limit from settings or use default
        $maxTasksPerDay = config('swiftkudi.rate_limits.tasks_per_day', 50);
        $maxTasksPerHour = config('swiftkudi.rate_limits.tasks_per_hour', 10);

        $todayCount = $this->taskRepository->getUserTodayTaskCount($userId);

        if ($todayCount >= $maxTasksPerDay) {
            return false;
        }

        // Check hourly rate limit
        $hourlyCount = Task::where('user_id', $userId)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return $hourlyCount < $maxTasksPerHour;
    }

    /**
     * Check if user has sufficient wallet balance.
     *
     * @param User $user
     * @param float $requiredAmount
     * @return array{sufficient: bool, message: string, balance: float}
     */
    protected function checkWalletBalance(User $user, float $requiredAmount): array
    {
        $wallet = $user->wallet ?? \App\Models\Wallet::firstOrCreate(
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

        if (!$wallet) {
            return [
                'sufficient' => false,
                'message' => 'Wallet setup incomplete. Please refresh and try again.',
                'balance' => 0,
            ];
        }

        $totalBalance = $wallet->withdrawable_balance + $wallet->promo_credit_balance;

        return [
            'sufficient' => $totalBalance >= $requiredAmount,
            'message' => "Insufficient balance. Required: ₦" . number_format($requiredAmount, 2) 
                . ", Available: ₦" . number_format($totalBalance, 2),
            'balance' => $totalBalance,
        ];
    }

    /**
     * Deduct amount from user's wallet.
     *
     * @param User $user
     * @param float $amount
     * @param Task $task
     * @return void
     */
    protected function deductFromWallet(User $user, float $amount, Task $task): void
    {
        $wallet = $user->wallet;

        if (!$wallet) {
            throw new \RuntimeException('Wallet not found');
        }

        DB::transaction(function () use ($wallet, $amount, $task, $user) {
            // Use withdrawable balance first, then promo credit
            $withdrawableUsed = min($amount, $wallet->withdrawable_balance);
            $promoUsed = $amount - $withdrawableUsed;

            if ($withdrawableUsed > 0) {
                $wallet->decrement('withdrawable_balance', $withdrawableUsed);
                
                // Record transaction
                $this->earnDeskService->recordTransaction(
                    $user,
                    'task_payment',
                    -$withdrawableUsed,
                    $wallet->id,
                    "Task payment: {$task->title}"
                );
            }

            if ($promoUsed > 0) {
                $wallet->decrement('promo_credit_balance', $promoUsed);
            }
        });
    }

    /**
     * Send notification when task is created.
     *
     * @param User $user
     * @param Task $task
     * @return void
     */
    protected function sendTaskCreatedNotification(User $user, Task $task): void
    {
        try {
            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $user,
                'Task Created Successfully',
                'Your task "' . $task->title . '" was created and is now being processed.',
                \App\Models\Notification::TYPE_NEW_TASK,
                ['task_id' => $task->id, 'action_url' => route('tasks.show', $task)],
                'notify_task_created',
                true
            );

            User::query()
                ->where('id', '!=', $user->id)
                ->where(function ($query) {
                    $query->where('is_admin', false)
                        ->orWhereNull('admin_role_id');
                })
                ->chunkById(200, function ($workers) use ($task) {
                    foreach ($workers as $worker) {
                        app(\App\Services\NotificationDispatchService::class)->sendToUser(
                            $worker,
                            'New Task Bundle Available',
                            'A new task bundle opportunity is now available: "' . $task->title . '".',
                            \App\Models\Notification::TYPE_NEW_TASK,
                            [
                                'task_id' => $task->id,
                                'task_title' => $task->title,
                                'action_url' => route('tasks.show', $task),
                            ],
                            'notify_task_bundle'
                        );
                    }
                });
        } catch (\Exception $e) {
            Log::warning('Failed to send task creation notification', [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sanitize data for logging (remove sensitive fields).
     *
     * @param array $data
     * @return array
     */
    protected function sanitizeForLogging(array $data): array
    {
        $sensitiveFields = ['password', 'token', 'secret', 'api_key'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***REDACTED***';
            }
        }

        return $data;
    }

    /**
     * Sanitize draft data for storage.
     *
     * @param array $data
     * @return array
     */
    protected function sanitizeDraftData(array $data): array
    {
        // Remove any sensitive data from draft
        return $this->sanitizeForLogging($data);
    }

    /**
     * Get category configuration for the create form.
     *
     * @return array
     */
    public function getCategoryConfig(): array
    {
        $categories = $this->taskRepository->getCategories();
        $groups = ['micro', 'ugc', 'referral', 'premium'];
        
        $config = [];
        
        foreach ($groups as $group) {
            $groupCats = $categories->where('task_type', $group);
            
            if ($groupCats->isEmpty()) {
                $config[$group] = [
                    'label' => $group,
                    'badge' => '',
                    'badgeText' => '',
                    'minBudget' => 0,
                    'platforms' => [],
                ];
                continue;
            }

            $platforms = [];
            foreach ($groupCats as $cat) {
                $pid = $cat->platform ?? 'other';
                if (!isset($platforms[$pid])) {
                    $platforms[$pid] = [
                        'id' => $pid,
                        'name' => $pid,
                        'icon' => '',
                        'tasks' => [],
                    ];
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

            $config[$group] = [
                'label' => $group,
                'badge' => '',
                'badgeText' => '',
                'minBudget' => 0,
                'platforms' => array_values($platforms),
            ];
        }

        return $config;
    }
}
