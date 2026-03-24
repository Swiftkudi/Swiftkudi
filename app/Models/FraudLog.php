<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FraudLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'description',
        'severity',
        'data',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'notes',
    ];

    protected $casts = [
        'data' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * Severity levels
     */
    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_CRITICAL = 'critical';

    /**
     * Fraud types
     */
    public const TYPE_DUPLICATE_ACCOUNT = 'duplicate_account';
    public const TYPE_SELF_TASK = 'self_task';
    public const TYPE_SUSPICIOUS_SUBMISSION = 'suspicious_submission';
    public const TYPE_FAKE_PROOF = 'fake_proof';
    public const TYPE_IP_VIOLATION = 'ip_violation';
    public const TYPE_RATE_LIMIT = 'rate_limit';
    public const TYPE_VOTE_MANIPULATION = 'vote_manipulation';
    public const TYPE_REFERRAL_ABUSE = 'referral_abuse';
    public const TYPE_MULTIPLE_ACCOUNTS = 'multiple_accounts';
    public const TYPE_SUSPICIOUS_ACTIVITY = 'suspicious_activity';

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Resolved by (Admin)
     */
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope: Unresolved
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope: By severity
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope: High severity
     */
    public function scopeHighSeverity($query)
    {
        return $query->whereIn('severity', [self::SEVERITY_HIGH, self::SEVERITY_CRITICAL]);
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
     * Mark as resolved
     */
    public function resolve(User $admin, string $notes = null): bool
    {
        $this->is_resolved = true;
        $this->resolved_at = now();
        $this->resolved_by = $admin->id;
        $this->notes = $notes;
        $this->save();

        return true;
    }

    /**
     * Get formatted severity
     */
    public function getSeverityLabelAttribute(): string
    {
        $labels = [
            self::SEVERITY_LOW => 'Low',
            self::SEVERITY_MEDIUM => 'Medium',
            self::SEVERITY_HIGH => 'High',
            self::SEVERITY_CRITICAL => 'Critical',
        ];

        return $labels[$this->severity] ?? 'Unknown';
    }

    /**
     * Get severity badge class
     */
    public function getSeverityBadgeClassAttribute(): string
    {
        $classes = [
            self::SEVERITY_LOW => 'badge-success',
            self::SEVERITY_MEDIUM => 'badge-warning',
            self::SEVERITY_HIGH => 'badge-danger',
            self::SEVERITY_CRITICAL => 'badge-dark',
        ];

        return $classes[$this->severity] ?? 'badge-secondary';
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Log suspicious activity
     */
    public static function logActivity(int $userId, string $type, string $description, array $data = [], string $severity = self::SEVERITY_MEDIUM): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'description' => $description,
            'severity' => $severity,
            'data' => $data,
            'is_resolved' => false,
        ]);
    }

    /**
     * Log duplicate account detection
     */
    public static function logDuplicateAccount(User $user, string $reason): self
    {
        return self::logActivity(
            $user->id,
            self::TYPE_DUPLICATE_ACCOUNT,
            'Potential duplicate account detected: ' . $reason,
            [
                'email' => $user->email,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
            self::SEVERITY_HIGH
        );
    }

    /**
     * Log self-task attempt
     */
    public static function logSelfTask(User $user, int $taskId): self
    {
        return self::logActivity(
            $user->id,
            self::TYPE_SELF_TASK,
            'User attempted to complete their own task',
            ['task_id' => $taskId],
            self::SEVERITY_MEDIUM
        );
    }

    /**
     * Log IP violation
     */
    public static function logIpViolation(User $user, string $reason): self
    {
        return self::logActivity(
            $user->id,
            self::TYPE_IP_VIOLATION,
            'IP address violation detected: ' . $reason,
            ['ip' => request()->ip()],
            self::SEVERITY_HIGH
        );
    }

    /**
     * Log suspicious submission
     */
    public static function logSuspiciousSubmission(User $user, int $submissionId, string $reason): self
    {
        return self::logActivity(
            $user->id,
            self::TYPE_SUSPICIOUS_SUBMISSION,
            'Suspicious submission detected: ' . $reason,
            ['submission_id' => $submissionId],
            self::SEVERITY_HIGH
        );
    }

    /**
     * Check for duplicate IP addresses
     */
    public static function hasDuplicateIp(int $excludeUserId = null): bool
    {
        $ip = request()->ip();
        
        $query = self::where('data->ip', $ip)
            ->where('type', self::TYPE_IP_VIOLATION);
        
        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }
        
        return $query->exists();
    }

    /**
     * Check for rate limit violation
     */
    public static function checkRateLimit(int $userId, int $maxSubmissions = 10, int $minutes = 60): bool
    {
        $recentSubmissions = self::where('user_id', $userId)
            ->where('type', self::TYPE_RATE_LIMIT)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
        
        return $recentSubmissions >= $maxSubmissions;
    }

    /**
     * Get unresolved count by severity
     */
    public static function getUnresolvedCounts(): array
    {
        return [
            'low' => self::unresolved()->bySeverity(self::SEVERITY_LOW)->count(),
            'medium' => self::unresolved()->bySeverity(self::SEVERITY_MEDIUM)->count(),
            'high' => self::unresolved()->bySeverity(self::SEVERITY_HIGH)->count(),
            'critical' => self::unresolved()->bySeverity(self::SEVERITY_CRITICAL)->count(),
            'total' => self::unresolved()->count(),
        ];
    }
}
