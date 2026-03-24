<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'worker_id',
        'proof_url',
        'proof_file',
        'note',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Relationship: Task
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Relationship: Worker (user who submitted)
     */
    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /**
     * Relationship: Reviewer
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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
     * Scope: Pending submissions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Approved submissions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: Rejected submissions
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope: For a specific task
     */
    public function scopeForTask($query, int $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Scope: By a specific worker
     */
    public function scopeByWorker($query, int $workerId)
    {
        return $query->where('worker_id', $workerId);
    }

    /**
     * Get formatted status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    /**
     * Get formatted status text
     */
    public function getStatusTextAttribute(): string
    {
        $texts = [
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];

        return $texts[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Approve the submission
     */
    public function approve(int $reviewerId): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->reviewed_by = $reviewerId;
        $this->reviewed_at = now();
        $this->save();

        return true;
    }

    /**
     * Reject the submission
     */
    public function reject(int $reviewerId, string $reason): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = self::STATUS_REJECTED;
        $this->rejection_reason = $reason;
        $this->reviewed_by = $reviewerId;
        $this->reviewed_at = now();
        $this->save();

        return true;
    }
}
