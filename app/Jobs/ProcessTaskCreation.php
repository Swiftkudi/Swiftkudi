<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\TaskCreationLog;
use App\Models\User;
use App\Services\TaskCreationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queue job for processing task creation asynchronously.
 * Used for heavy operations that might take longer to process.
 */
class ProcessTaskCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 120;

    /**
     * User who is creating the task.
     *
     * @var User
     */
    private $user;

    /**
     * Task data.
     *
     * @var array
     */
    private $data;

    /**
     * Idempotency token.
     *
     * @var string
     */
    private $idempotencyToken;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param array $data
     * @param string $idempotencyToken
     * @return void
     */
    public function __construct(User $user, array $data, string $idempotencyToken)
    {
        $this->user = $user;
        $this->data = $data;
        $this->idempotencyToken = $idempotencyToken;
    }

    /**
     * Execute the job.
     *
     * @param TaskCreationService $service
     * @return void
     */
    public function handle(TaskCreationService $service): void
    {
        Log::info('Processing queued task creation', [
            'user_id' => $this->user->id,
            'token' => $this->idempotencyToken,
            'attempt' => $this->attempts(),
        ]);

        // Get the original request data for context
        $creationLog = TaskCreationLog::where('token', $this->idempotencyToken)
            ->where('user_id', $this->user->id)
            ->first();

        if (!$creationLog) {
            Log::warning('Task creation log not found for queued job', [
                'token' => $this->idempotencyToken,
                'user_id' => $this->user->id,
            ]);
            return;
        }

        try {
            // Note: This job just logs progress
            // The actual task creation is done synchronously in the service
            // This job is mainly for monitoring and future async processing
            
            $creationLog->update([
                'status' => TaskCreationLog::STATUS_PROCESSING,
                'response_data' => ['job_started' => true, 'attempt' => $this->attempts()],
            ]);

            Log::info('Task creation job completed', [
                'task_id' => $creationLog->task_id,
                'token' => $this->idempotencyToken,
            ]);

        } catch (\Exception $e) {
            Log::error('Task creation job failed', [
                'user_id' => $this->user->id,
                'token' => $this->idempotencyToken,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            $creationLog->markFailed($e->getMessage());

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Task creation job permanently failed', [
            'user_id' => $this->user->id,
            'token' => $this->idempotencyToken,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Update the creation log to reflect failure
        $creationLog = TaskCreationLog::where('token', $this->idempotencyToken)
            ->where('user_id', $this->user->id)
            ->first();

        if ($creationLog) {
            $creationLog->markFailed($exception->getMessage());
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return [
            'task_creation',
            'user:' . $this->user->id,
        ];
    }
}
