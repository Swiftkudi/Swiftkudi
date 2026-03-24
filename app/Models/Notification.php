<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    // Use a distinct table name to avoid colliding with Laravel's notifications table
    protected $table = 'user_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Notification types
     */
    public const TYPE_TASK_APPROVED = 'task_approved';
    public const TYPE_TASK_REJECTED = 'task_rejected';
    public const TYPE_NEW_TASK = 'new_task';
    public const TYPE_EARNINGS = 'earnings';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_LEVEL_UP = 'level_up';
    public const TYPE_BADGE_EARNED = 'badge_earned';
    public const TYPE_STREAK_REWARD = 'streak_reward';
    public const TYPE_TASK_EXPIRY = 'task_expiry';
    public const TYPE_REFERRAL = 'referral';
    public const TYPE_SYSTEM = 'system';

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Recent
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark as read
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return false;
        }

        $this->is_read = true;
        $this->read_at = now();
        $this->save();

        return true;
    }

    /**
     * Get formatted created date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get icon based on type
     */
    public function getIconAttribute(): string
    {
        $icons = [
            self::TYPE_TASK_APPROVED => 'fas fa-check-circle',
            self::TYPE_TASK_REJECTED => 'fas fa-times-circle',
            self::TYPE_NEW_TASK => 'fas fa-tasks',
            self::TYPE_EARNINGS => 'fas fa-naira-sign',
            self::TYPE_WITHDRAWAL => 'fas fa-bank',
            self::TYPE_LEVEL_UP => 'fas fa-level-up-alt',
            self::TYPE_BADGE_EARNED => 'fas fa-medal',
            self::TYPE_STREAK_REWARD => 'fas fa-fire',
            self::TYPE_TASK_EXPIRY => 'fas fa-exclamation-triangle',
            self::TYPE_REFERRAL => 'fas fa-users',
            self::TYPE_SYSTEM => 'fas fa-bell',
        ];

        return $icons[$this->type] ?? 'fas fa-bell';
    }

    /**
     * Get color based on type
     */
    public function getColorAttribute(): string
    {
        $colors = [
            self::TYPE_TASK_APPROVED => 'success',
            self::TYPE_TASK_REJECTED => 'danger',
            self::TYPE_NEW_TASK => 'primary',
            self::TYPE_EARNINGS => 'success',
            self::TYPE_WITHDRAWAL => 'info',
            self::TYPE_LEVEL_UP => 'warning',
            self::TYPE_BADGE_EARNED => 'warning',
            self::TYPE_STREAK_REWARD => 'warning',
            self::TYPE_TASK_EXPIRY => 'danger',
            self::TYPE_REFERRAL => 'info',
            self::TYPE_SYSTEM => 'secondary',
        ];

        return $colors[$this->type] ?? 'secondary';
    }

    /**
     * Send notification to user
     */
    public static function sendTo(User $user, string $title, string $message, string $type = self::TYPE_SYSTEM, array $data = []): self
    {
        return self::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Notify task approved
     */
    public static function taskApproved(User $user, Task $task, float $amount): self
    {
        return self::sendTo(
            $user,
            'Task Approved! 🎉',
            'Your submission for "' . $task->title . '" has been approved. You earned ₦' . number_format($amount, 2),
            self::TYPE_TASK_APPROVED,
            ['task_id' => $task->id, 'amount' => $amount]
        );
    }

    /**
     * Notify task rejected
     */
    public static function taskRejected(User $user, Task $task, string $reason): self
    {
        return self::sendTo(
            $user,
            'Task Submission Rejected',
            'Your submission for "' . $task->title . '" was rejected: ' . $reason,
            self::TYPE_TASK_REJECTED,
            ['task_id' => $task->id, 'reason' => $reason]
        );
    }

    /**
     * Notify new task available
     */
    public static function newTaskAvailable(User $user, Task $task): self
    {
        return self::sendTo(
            $user,
            'New Task Available!',
            'New task: "' . $task->title . '" - Earn up to ₦' . number_format($task->worker_reward_per_task, 2),
            self::TYPE_NEW_TASK,
            ['task_id' => $task->id]
        );
    }

    /**
     * Notify earnings
     */
    public static function earningsUpdate(User $user, float $amount): self
    {
        return self::sendTo(
            $user,
            'Earnings Received! 💰',
            'You received ₦' . number_format($amount, 2) . ' in your wallet.',
            self::TYPE_EARNINGS,
            ['amount' => $amount]
        );
    }

    /**
     * Notify withdrawal
     */
    public static function withdrawalUpdate(User $user, float $amount, string $status): self
    {
        $amountFormatted = number_format($amount, 2);
        
        if ($status === 'completed') {
            $message = 'Your withdrawal of ₦' . $amountFormatted . ' has been completed.';
        } elseif ($status === 'pending') {
            $message = 'Your withdrawal of ₦' . $amountFormatted . ' is pending approval.';
        } elseif ($status === 'rejected') {
            $message = 'Your withdrawal of ₦' . $amountFormatted . ' was rejected.';
        } else {
            $message = 'Withdrawal status updated.';
        }

        return self::sendTo(
            $user,
            'Withdrawal Update',
            $message,
            self::TYPE_WITHDRAWAL,
            ['amount' => $amount, 'status' => $status]
        );
    }

    /**
     * Notify task expiry reminder
     */
    public static function taskExpiryReminder(User $user, Task $task, int $hoursLeft): self
    {
        return self::sendTo(
            $user,
            'Task Expiring Soon! ⏰',
            'Your task "' . $task->title . '" expires in ' . $hoursLeft . ' hours.',
            self::TYPE_TASK_EXPIRY,
            ['task_id' => $task->id, 'hours_left' => $hoursLeft]
        );
    }
}
