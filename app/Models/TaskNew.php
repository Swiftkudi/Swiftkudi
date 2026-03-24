<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TaskNew extends Model
{
    use HasFactory;

    protected $table = 'tasks_new';

    protected $fillable = [
        'user_id',
        'category',
        'title',
        'description',
        'proof_instructions',
        'budget_total',
        'reward_per_user',
        'max_workers',
        'workers_completed_count',
        'workers_accepted_count',
        'status',
        'escrow_balance',
        'total_paid',
        'commission_earned',
        'expires_at',
        'activated_at',
    ];

    protected $casts = [
        'budget_total' => 'decimal:2',
        'reward_per_user' => 'decimal:2',
        'workers_completed_count' => 'integer',
        'workers_accepted_count' => 'integer',
        'escrow_balance' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'commission_earned' => 'decimal:2',
        'expires_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_FUNDING = 'pending_funding';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    // Categories
    public const CATEGORY_MICRO = 'micro';
    public const CATEGORY_UGC = 'ugc';
    public const CATEGORY_GROWTH = 'growth';
    public const CATEGORY_PREMIUM = 'premium';

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get submissions
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(TaskSubmissionNew::class, 'task_id');
    }

    /**
     * Get pending submissions
     */
    public function pendingSubmissions(): HasMany
    {
        return $this->hasMany(TaskSubmissionNew::class, 'task_id')
            ->where('status', 'pending');
    }

    /**
     * Get wallet transactions
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(TaskWalletTransaction::class, 'task_id');
    }

    /**
     * Scope: Active tasks
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: By category
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Available tasks (active and not full)
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereRaw('workers_accepted_count < max_workers');
    }

    /**
     * Check if task is full
     */
    public function isFull(): bool
    {
        return $this->workers_accepted_count >= $this->max_workers;
    }

    /**
     * Check if task is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Calculate required funding
     */
    public function getRequiredFunding(): float
    {
        return $this->reward_per_user * $this->max_workers;
    }

    /**
     * Get remaining slots
     */
    public function getRemainingSlots(): int
    {
        return max(0, $this->max_workers - $this->workers_accepted_count);
    }

    /**
     * Check if worker has already submitted
     */
    public function hasWorkerSubmitted(int $workerId): bool
    {
        return $this->submissions()->where('worker_id', $workerId)->exists();
    }

    /**
     * Get submission by worker
     */
    public function getWorkerSubmission(int $workerId)
    {
        return $this->submissions()->where('worker_id', $workerId)->first();
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
        ]);
    }

    /**
     * Check if can auto-complete
     */
    public function shouldAutoComplete(): bool
    {
        return $this->workers_completed_count >= $this->max_workers;
    }
}
