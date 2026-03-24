<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TaskCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'bundle_id',
        'user_id',
        'status',
        'proof_data',
        'proof_description',
        'proof_screenshots',
        'worker_notes',
        'reward_amount',
        'promo_credit_earned',
        'xp_earned',
        'admin_notes',
        'rejection_reason',
        'ip_address',
        'user_agent',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => 'string',
        'reward_amount' => 'decimal:2',
        'promo_credit_earned' => 'decimal:2',
        'xp_earned' => 'integer',
        'proof_screenshots' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Status values
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FLAGGED = 'flagged'; // Flagged for manual review

    /**
     * Status list
     */
    public const STATUSES = [
        self::STATUS_PENDING => 'Pending Review',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_FLAGGED => 'Flagged',
    ];

    /**
     * Rejection reasons
     */
    public const REJECTION_REASONS = [
        'invalid_proof' => 'Invalid or unclear proof',
        'fake_account' => 'Suspicious/fake account detected',
        'duplicate_submission' => 'Duplicate submission',
        'task_not_completed' => 'Task requirements not met',
        'poor_quality' => 'Poor quality submission',
        'wrong_platform' => 'Wrong platform submitted',
        'expired_link' => 'Link/URL is expired or invalid',
        'self_task' => 'Cannot complete own task',
        'violation' => 'Platform rules violation',
        'other' => 'Other',
    ];

    /**
     * Relationship: Task
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Relationship: Bundle (if part of bundle)
     */
    public function bundle()
    {
        return $this->belongsTo(TaskBundle::class);
    }

    /**
     * Relationship: Worker (User)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if flagged
     */
    public function isFlagged(): bool
    {
        return $this->status === self::STATUS_FLAGGED;
    }

    /**
     * Mark as approved
     *
     * Note: This method intentionally does NOT perform wallet transfers or award earnings.
     * Payment and escrow handling must be performed by the service layer (SwiftKudiService)
     * which will call this method inside the same transaction after validating escrow.
     */
    public function approve(float $rewardAmount = null, string $notes = null): bool
    {
        // Use task reward if not specified
        if ($rewardAmount === null) {
            $rewardAmount = $this->task->worker_reward_per_task ?? 0;
        }

        $this->status = self::STATUS_APPROVED;

        // Only set columns that actually exist in the table to avoid DB errors
        if (Schema::hasColumn($this->getTable(), 'reward_amount')) {
            $this->reward_amount = $rewardAmount;
        }

        if (Schema::hasColumn($this->getTable(), 'promo_credit_earned')) {
            $this->promo_credit_earned = round($rewardAmount * 0.20, 2); // 20% promo credit
        }

        if (Schema::hasColumn($this->getTable(), 'xp_earned')) {
            $this->xp_earned = $this->task->difficulty_level === 'hard' ? 25 : 
                              ($this->task->difficulty_level === 'medium' ? 15 : 10);
        }

        if (Schema::hasColumn($this->getTable(), 'reviewed_at')) {
            $this->reviewed_at = now();
        }

        if ($notes && Schema::hasColumn($this->getTable(), 'admin_notes')) {
            $this->admin_notes = $notes;
        }

        // Only clear rejection_reason if the column exists
        if (Schema::hasColumn($this->getTable(), 'rejection_reason')) {
            $this->rejection_reason = null;
        }

        $this->save();
        
        // Update task completed count (if relationship present)
        if ($this->task) {
            $this->task->completed_count++;
            $this->task->save();
        }
        
        // Log to fraud tracking if available
        if (method_exists(FraudLog::class, 'log')) {
            FraudLog::log($this, 'completion_approved', 'Submission approved successfully');
        }
        
        return true;
    }

    /**
     * Mark as rejected
     */
    public function reject(string $reason, string $notes = null): bool
    {
        $this->status = self::STATUS_REJECTED;

        if (Schema::hasColumn($this->getTable(), 'rejection_reason')) {
            $this->rejection_reason = $reason;
        }

        if ($notes && Schema::hasColumn($this->getTable(), 'admin_notes')) {
            $this->admin_notes = $notes;
        }

        if (Schema::hasColumn($this->getTable(), 'reviewed_at')) {
            $this->reviewed_at = now();
        }

        $this->save();
        
        // Log to fraud tracking if available
        if (method_exists(FraudLog::class, 'log')) {
            FraudLog::log($this, 'completion_rejected', "Rejected: {$reason}");
        }
        
        return true;
    }

    /**
     * Flag for manual review
     */
    public function flag(string $reason): bool
    {
        $this->status = self::STATUS_FLAGGED;

        if (Schema::hasColumn($this->getTable(), 'admin_notes')) {
            $this->admin_notes = $reason;
        }

        $this->save();
        
        // Log to fraud tracking if available
        if (method_exists(FraudLog::class, 'log')) {
            FraudLog::log($this, 'completion_flagged', "Flagged for review: {$reason}");
        }
        
        return true;
    }

    /**
     * Submit task
     */
    public function submit(array $proofData, string $notes = null): bool
    {
        $this->status = self::STATUS_PENDING;

        if (Schema::hasColumn($this->getTable(), 'proof_data')) {
            $this->proof_data = json_encode($proofData);
        }

        if ($notes && Schema::hasColumn($this->getTable(), 'proof_description')) {
            $this->proof_description = $notes;
        }

        if (Schema::hasColumn($this->getTable(), 'submitted_at')) {
            $this->submitted_at = now();
        }

        if (Schema::hasColumn($this->getTable(), 'ip_address')) {
            $this->ip_address = request()->ip();
        }

        if (Schema::hasColumn($this->getTable(), 'user_agent')) {
            $this->user_agent = request()->userAgent();
        }

        $this->save();
        
        // Log to fraud tracking if available
        if (method_exists(FraudLog::class, 'log')) {
            FraudLog::log($this, 'submission', 'Task submitted for review');
        }
        
        return true;
    }

    /**
     * Get formatted reward
     */
    public function getFormattedRewardAttribute(): string
    {
        return '₦' . number_format($this->reward_amount ?? 0, 2);
    }

    /**
     * Get formatted promo credit
     */
    public function getFormattedPromoCreditAttribute(): string
    {
        return '₦' . number_format($this->promo_credit_earned ?? 0, 2);
    }

    /**
     * Get formatted XP
     */
    public function getFormattedXpAttribute(): string
    {
        return '+' . ($this->xp_earned ?? 0) . ' XP';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'badge-warning';
            case self::STATUS_APPROVED:
                return 'badge-success';
            case self::STATUS_REJECTED:
                return 'badge-danger';
            case self::STATUS_FLAGGED:
                return 'badge-info';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Unknown';
    }

    /**
     * Get rejection reason label
     */
    public function getRejectionReasonLabelAttribute(): string
    {
        return self::REJECTION_REASONS[$this->rejection_reason] ?? $this->rejection_reason;
    }

    /**
     * Scope: Pending review
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_FLAGGED]);
    }

    /**
     * Scope: Approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: Rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope: By user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: By task
     */
    public function scopeByTask($query, int $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Scope: By bundle
     */
    public function scopeByBundle($query, int $bundleId)
    {
        return $query->where('bundle_id', $bundleId);
    }

    /**
     * Scope: Recent
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Flagged for review
     */
    public function scopeFlagged($query)
    {
        return $query->where('status', self::STATUS_FLAGGED);
    }

    /**
     * Get time since submission
     */
    public function getTimeSinceSubmissionAttribute(): string
    {
        if (!$this->submitted_at) {
            return 'N/A';
        }
        return $this->submitted_at->diffForHumans();
    }
}
