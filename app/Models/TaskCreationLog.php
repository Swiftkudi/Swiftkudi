<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for tracking task creation requests (idempotency).
 * Prevents duplicate submissions and enables audit logging.
 *
 * @property int $id
 * @property string $token
 * @property int $user_id
 * @property int|null $task_id
 * @property string $status
 * @property array $request_payload
 * @property array|null $response_data
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $failure_reason
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TaskCreationLog extends Model
{
    use HasFactory;

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DUPLICATE = 'duplicate';

    /**
     * The table associated with the model.
     */
    protected $table = 'task_creation_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'token',
        'user_id',
        'task_id',
        'status',
        'request_payload',
        'response_data',
        'ip_address',
        'user_agent',
        'failure_reason',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'user_id' => 'integer',
        'task_id' => 'integer',
        'request_payload' => 'array',
        'response_data' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Status options for the status field.
     */
    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_PROCESSING => 'Processing',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_FAILED => 'Failed',
        self::STATUS_DUPLICATE => 'Duplicate',
    ];

    /**
     * Relationship: User who initiated the task creation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: The created task (if successful).
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Check if this creation request is still valid (not expired).
     */
    public function isValid(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Mark the log as completed successfully.
     */
    public function markCompleted(Task $task, array $responseData = []): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'task_id' => $task->id,
            'response_data' => $responseData,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the log as failed.
     */
    public function markFailed(string $reason, array $responseData = []): void
    {
        $safeReason = mb_substr(trim($reason), 0, 250);

        $this->update([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $safeReason,
            'response_data' => $responseData,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the log as a duplicate.
     */
    public function markDuplicate(): void
    {
        $this->update([
            'status' => self::STATUS_DUPLICATE,
            'completed_at' => now(),
        ]);
    }

    /**
     * Check if a token already exists (for any non-terminal status).
     */
    public static function tokenExists(string $token): bool
    {
        return self::where('token', $token)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING, self::STATUS_FAILED])
            ->exists();
    }

    /**
     * Find by token.
     */
    public static function findByToken(string $token): ?self
    {
        return self::where('token', $token)->first();
    }

    /**
     * Get recent logs for a user.
     */
    public static function getRecentForUser(int $userId, int $limit = 10)
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
