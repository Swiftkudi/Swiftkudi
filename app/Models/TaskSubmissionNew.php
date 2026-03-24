<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSubmissionNew extends Model
{
    use HasFactory;

    protected $table = 'task_submissions_new';

    protected $fillable = [
        'task_id',
        'worker_id',
        'proof_data',
        'notes',
        'status',
        'rejection_reason',
        'reviewed_at',
        'paid_at',
    ];

    protected $casts = [
        'proof_data' => 'array',
        'reviewed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Get the task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskNew::class, 'task_id');
    }

    /**
     * Get the worker
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
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
     * Mark as approved
     */
    public function markApproved(): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_at' => now(),
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark as rejected
     */
    public function markRejected(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_at' => now(),
        ]);
    }
}
