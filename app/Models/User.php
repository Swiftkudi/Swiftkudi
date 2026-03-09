<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if ($user->isAdmin() && empty($user->email_verified_at)) {
                $user->email_verified_at = now();
            }
        });

        static::updating(function (self $user): void {
            if ($user->isAdmin() && empty($user->email_verified_at)) {
                $user->email_verified_at = now();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
        'provider',
        'provider_id',
        'phone',
        'password',
        'level',
        'experience_points',
        'daily_streak',
        'last_activity_at',
        'referral_code',
        'referred_by',
        'trial_ends_at',
        'is_admin',
        'admin_role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'level' => 'integer',
        'experience_points' => 'integer',
        'daily_streak' => 'integer',
        'last_activity_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification());
    }

    /**
     * Level thresholds for experience points
     */
    public const LEVEL_THRESHOLDS = [
        1 => 0,
        2 => 100,
        3 => 300,
        4 => 600,
        5 => 1000,
        6 => 1500,
        7 => 2500,
        8 => 4000,
        9 => 6000,
        10 => 10000,
    ];

    /**
     * Daily streak rewards
     */
    public const STREAK_REWARD_DAYS = [3, 7, 14, 30]; // Days that give rewards
    public const STREAK_REWARD_AMOUNTS = [
        3 => 50,   // ₦50 for 3-day streak
        7 => 100,  // ₦100 for 7-day streak
        14 => 200, // ₦200 for 14-day streak
        30 => 500, // ₦500 for 30-day streak
    ];

    /**
     * Relationship: Wallet
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Relationship: Referrals (users this user referred)
     */
    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Relationship: Referred by
     */
    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Relationship: Tasks created by this user
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id');
    }

    /**
     * Relationship: Task completions
     */
    public function taskCompletions()
    {
        return $this->hasMany(TaskCompletion::class, 'user_id');
    }

    /**
     * Backwards-compatible alias for older snake_case relation calls used in some views.
     * Allows calls like $user->task_completions() to continue working until views are fully updated.
     */
    public function task_completions()
    {
        return $this->taskCompletions();
    }

    /**
     * Relationship: Badges earned
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')
            ->using(UserBadge::class);
    }

    /**
     * Relationship: Withdrawals
     */
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class, 'user_id');
    }

    /**
     * Relationship: Wallet ledger entries
     */
    public function ledgerEntries()
    {
        return $this->hasMany(WalletLedger::class, 'user_id');
    }

    /**
     * Relationship: Notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Relationship: Unread notifications
     */
    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id')
            ->where('is_read', false);
    }

    /**
     * Relationship: Admin Role
     */
    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class, 'admin_role_id');
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true || $this->adminRole !== null;
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        if (!$this->is_admin) {
            return false;
        }
        
        // Check if user has super_admin role
        if ($this->adminRole && $this->adminRole->name === AdminRole::ROLE_SUPER_ADMIN) {
            return true;
        }
        
        // Fallback: if is_admin is true but no role, treat as super admin
        return true;
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->isAdmin()) {
            return false;
        }
        
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        return $this->adminRole && $this->adminRole->hasPermission($permission);
    }

    /**
     * Generate a unique referral code
     *
     * Accepts an optional preferred string (for example a username) to produce
     * a human-friendly code. Falls back to the previous random code when a
     * friendly base cannot be derived.
     *
     * @param string|null $preferred
     * @return string
     */
    public static function generateReferralCode(string $preferred = null): string
    {
        // If a preferred string is provided, sanitize to alphanumeric and use as base
        $base = null;
        if ($preferred) {
            $base = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($preferred));
            $base = substr($base, 0, 8);
        }

        // If no preferred provided, attempt to use the authenticated user's name (if available)
        if (!$base && function_exists('auth') && auth()->check()) {
            $base = preg_replace('/[^A-Za-z0-9]/', '', strtoupper(auth()->user()->name ?? ''));
            $base = substr($base, 0, 8);
        }

        // If we have a human-friendly base, append a short random suffix and ensure uniqueness
        if ($base) {
            do {
                $suffix = strtoupper(substr(md5(uniqid((string) rand(), true)), 0, 4));
                $code = $base . $suffix;
            } while (self::where('referral_code', $code)->exists());

            return $code;
        }

        // Fallback to original behaviour (fully random code)
        do {
            $code = 'ED' . strtoupper(substr(md5(uniqid() . microtime()), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Check if user can perform tasks (activated)
     */
    public function canPerformTasks(): bool
    {
        return $this->wallet && $this->wallet->is_activated;
    }

    /**
     * Check if user can create tasks (has wallet)
     */
    public function canCreateTasks(): bool
    {
        return $this->wallet !== null;
    }

    /**
     * Get current level based on experience points
     */
    public function getCurrentLevel(): int
    {
        $xp = $this->experience_points;
        $level = 1;
        
        foreach (self::LEVEL_THRESHOLDS as $lvl => $threshold) {
            if ($xp >= $threshold) {
                $level = $lvl;
            }
        }
        
        return $level;
    }

    /**
     * Get XP needed for next level
     */
    public function getXpForNextLevel(): ?int
    {
        $currentLevel = $this->getCurrentLevel();
        $nextLevel = $currentLevel + 1;
        
        return self::LEVEL_THRESHOLDS[$nextLevel] ?? null;
    }

    /**
     * Get XP progress to next level
     */
    public function getXpProgress(): array
    {
        $currentLevel = $this->getCurrentLevel();
        $currentThreshold = self::LEVEL_THRESHOLDS[$currentLevel] ?? 0;
        $nextThreshold = self::LEVEL_THRESHOLDS[$currentLevel + 1] ?? $currentThreshold;
        
        $xpInCurrentLevel = $this->experience_points - $currentThreshold;
        $xpNeeded = $nextThreshold - $currentThreshold;
        
        $percentage = $xpNeeded > 0 ? ($xpInCurrentLevel / $xpNeeded) * 100 : 100;
        
        return [
            'current_level' => $currentLevel,
            'next_level' => $currentLevel + 1,
            'xp_current' => $this->experience_points,
            'xp_needed' => $xpNeeded,
            'xp_progress' => $xpInCurrentLevel,
            'percentage' => min(100, $percentage),
        ];
    }

    /**
     * Add experience points and handle level up
     */
    public function addExperience(int $points): bool
    {
        $oldLevel = $this->level;
        $this->experience_points += $points;
        $this->level = $this->getCurrentLevel();
        $this->save();

        // Check for level up notification
        if ($this->level > $oldLevel) {
            $this->sendNotification(
                'Level Up!',
                'Congratulations! You reached Level ' . $this->level . '. New tasks are now available!',
                'level_up'
            );
        }

        // Check for new badges
        $this->checkBadges();

        return $this->level > $oldLevel;
    }

    /**
     * Update daily streak and award rewards
     */
    public function updateDailyStreak(): bool
    {
        $today = now()->startOfDay();
        $lastActivity = $this->last_activity_at ? $this->last_activity_at->startOfDay() : null;
        
        $oldStreak = $this->daily_streak;
        
        if (!$lastActivity) {
            // First activity
            $this->daily_streak = 1;
        } elseif ($lastActivity->isToday()) {
            // Already active today, no change
            return false;
        } elseif ($lastActivity->isYesterday()) {
            // Consecutive day
            $this->daily_streak++;
        } else {
            // Streak broken
            $this->daily_streak = 1;
        }
        
        $this->last_activity_at = now();
        $this->save();

        // Award streak reward if milestone reached
        if (in_array($this->daily_streak, self::STREAK_REWARD_DAYS)) {
            $reward = self::STREAK_REWARD_AMOUNTS[$this->daily_streak];
            $this->awardStreakReward($reward);
        }

        return true;
    }

    /**
     * Award streak reward
     */
    public function awardStreakReward(float $amount): bool
    {
        if (!$this->wallet) {
            return false;
        }

        $this->wallet->addPromoCredit($amount, 'streak_reward');

        $this->sendNotification(
            'Daily Streak Reward! 🎉',
            'You earned ₦' . number_format($amount, 2) . ' promo credit for your ' . $this->daily_streak . '-day streak!',
            'streak_reward'
        );

        return true;
    }

    /**
     * Check and award badges
     */
    public function checkBadges(): array
    {
        $newBadges = [];
        $badges = Badge::all();

        foreach ($badges as $badge) {
            // Skip if already earned
            if ($this->badges->contains($badge)) {
                continue;
            }

            // Check badge requirements
            if ($this->meetsBadgeRequirements($badge)) {
                $this->awardBadge($badge);
                $newBadges[] = $badge;
            }
        }

        return $newBadges;
    }

    /**
     * Check if user meets badge requirements
     */
    public function meetsBadgeRequirements(Badge $badge): bool
    {
        $requirements = $badge->requirements ?? [];

        foreach ($requirements as $type => $value) {
            switch ($type) {
                case 'tasks_completed':
                    if ($this->taskCompletions()->approved()->count() < $value) {
                        return false;
                    }
                    break;
                case 'total_earnings':
                    if (($this->wallet->total_earned ?? 0) < $value) {
                        return false;
                    }
                    break;
                case 'level':
                    if ($this->level < $value) {
                        return false;
                    }
                    break;
                case 'streak':
                    if ($this->daily_streak < $value) {
                        return false;
                    }
                    break;
                case 'referrals':
                    if ($this->referrals()->count() < $value) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Award a badge to user
     */
    public function awardBadge(Badge $badge): bool
    {
        $this->badges()->attach($badge->id, ['earned_at' => now()]);

        $this->sendNotification(
            'New Badge Earned! 🏆',
            'You earned the "' . $badge->name . '" badge!',
            'badge_earned',
            ['badge_id' => $badge->id]
        );

        return true;
    }

    /**
     * Send notification to user
     */
    public function sendNotification(string $title, string $message, string $type = 'info', array $data = []): Notification
    {
        $notification = app(\App\Services\NotificationDispatchService::class)
            ->createInAppNotification($this, $title, $message, $type, $data);

        if ($notification instanceof Notification) {
            return $notification;
        }

        return Notification::create([
            'user_id' => $this->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Get unread notification count
     */
    public function getUnreadNotificationCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead(): bool
    {
        $this->unreadNotifications()->update(['is_read' => true]);
        return true;
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalance(): array
    {
        if (!$this->wallet) {
            return [
                'withdrawable' => '₦0.00',
                'promo_credit' => '₦0.00',
                'total' => '₦0.00',
            ];
        }
        
        $withdrawable = $this->wallet->withdrawable_balance;
        $promo = $this->wallet->promo_credit_balance;
        
        return [
            'withdrawable' => '₦' . number_format($withdrawable, 2),
            'promo_credit' => '₦' . number_format($promo, 2),
            'total' => '₦' . number_format($withdrawable + $promo, 2),
        ];
    }

    /**
     * Get minimum withdrawal amount
     */
    public static function getMinimumWithdrawal(): int
    {
        return 3000; // ₦3,000
    }

    /**
     * Check if user can withdraw
     */
    public function canWithdraw(): bool
    {
        if (!$this->wallet || !$this->wallet->is_activated) {
            return false;
        }
        
        return $this->wallet->withdrawable_balance >= self::getMinimumWithdrawal();
    }

    /**
     * Get referrer bonus amount
     */
    public static function getReferrerBonus(): int
    {
        return 500; // ₦500
    }

    /**
     * Get activation fee
     */
    public static function getActivationFee(): int
    {
        return 1000; // ₦1,000
    }

    /**
     * Get referred user activation fee
     */
    public static function getReferredActivationFee(): int
    {
        return 2000; // ₦2,000
    }

    /**
     * Get total earnings
     */
    public function getTotalEarnings(): float
    {
        return $this->wallet->total_earned ?? 0;
    }

    /**
     * Get completed tasks count
     */
    public function getCompletedTasksCount(): int
    {
        return $this->taskCompletions()->approved()->count();
    }

    /**
     * Get leaderboard position
     */
    public function getLeaderboardPosition(): ?int
    {
        $position = self::where('experience_points', '>', $this->experience_points)
            ->count();
        
        return $position + 1;
    }
}
