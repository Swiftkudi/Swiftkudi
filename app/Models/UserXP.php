<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

class UserXP extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_xp',
        'current_level',
        'xp_for_current_level',
        'xp_for_next_level',
        'tasks_completed',
        'referrals_made',
        'current_streak',
        'longest_streak',
        'last_activity_date',
        'streak_start_date',
    ];

    protected $casts = [
        'total_xp' => 'integer',
        'current_level' => 'integer',
        'xp_for_current_level' => 'integer',
        'xp_for_next_level' => 'integer',
        'tasks_completed' => 'integer',
        'referrals_made' => 'integer',
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'last_activity_date' => 'date',
        'streak_start_date' => 'date',
    ];

    /**
     * Action types constants
     */
    const ACTION_TASK_COMPLETED = 'task_completed';
    const ACTION_TASK_APPROVED = 'task_approved';
    const ACTION_REFERRAL = 'referral';
    const ACTION_REFERRAL_ACTIVATED = 'referral_activated';
    const ACTION_DAILY_STREAK = 'daily_streak';
    const ACTION_MILESTONE = 'milestone';
    const ACTION_LEVEL_UP = 'level_up';
    const ACTION_FIRST_TASK = 'first_task';
    const ACTION_BONUS = 'bonus';

    /**
     * Default level thresholds (XP needed to reach each level)
     */
    public static function getLevelThresholds(): array
    {
        return SystemSetting::getArray('xp_level_thresholds', [
            1 => 0,
            2 => 100,
            3 => 250,
            4 => 500,
            5 => 1000,
            6 => 2000,
            7 => 3500,
            8 => 5500,
            9 => 8000,
            10 => 12000,
            11 => 17000,
            12 => 23000,
            13 => 30000,
            14 => 38000,
            15 => 47000,
            16 => 57000,
            17 => 68000,
            18 => 80000,
            19 => 95000,
            20 => 115000,
        ]);
    }

    /**
     * Get the user this XP belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get XP history for this user
     */
    public function history()
    {
        return $this->hasMany(XPHistory::class, 'user_id', 'user_id');
    }

    /**
     * Calculate level from total XP
     */
    public static function calculateLevel(int $totalXP): int
    {
        $thresholds = self::getLevelThresholds();
        $level = 1;

        foreach ($thresholds as $lvl => $threshold) {
            if ($totalXP >= $threshold) {
                $level = $lvl;
            } else {
                break;
            }
        }

        return $level;
    }

    /**
     * Calculate XP needed for next level
     */
    public static function getXPForLevel(int $level): int
    {
        $thresholds = self::getLevelThresholds();
        return $thresholds[$level] ?? ($thresholds[count($thresholds)] ?? 100000 + ($level - count($thresholds)) * 25000);
    }

    /**
     * Get progress percentage to next level
     */
    public function getProgressToNextLevel(): float
    {
        if ($this->current_level >= 20) {
            return 100.0; // Max level
        }

        $xpInCurrentLevel = $this->total_xp - $this->xp_for_current_level;
        $xpNeeded = $this->xp_for_next_level - $this->xp_for_current_level;

        if ($xpNeeded <= 0) {
            return 100.0;
        }

        return round(($xpInCurrentLevel / $xpNeeded) * 100, 1);
    }

    /**
     * Add XP to user and handle level ups
     */
    public function addXP(int $amount, string $actionType, ?string $description = null, ?int $relatedId = null): array
    {
        $oldLevel = $this->current_level;
        $this->total_xp += $amount;
        $this->last_activity_date = now()->toDateString();

        // Calculate new level
        $newLevel = self::calculateLevel($this->total_xp);
        $this->current_level = $newLevel;

        // Update XP thresholds for current and next level
        $this->xp_for_current_level = self::getXPForLevel($newLevel);
        $this->xp_for_next_level = self::getXPForLevel($newLevel + 1);

        // Update streak if daily streak action
        if ($actionType === self::ACTION_DAILY_STREAK) {
            $this->updateStreak();
        }

        $this->save();

        // Record XP history
        XPHistory::create([
            'user_id' => $this->user_id,
            'xp_amount' => $amount,
            'action_type' => $actionType,
            'description' => $description,
            'related_id' => $relatedId,
        ]);

        $result = [
            'xp_added' => $amount,
            'new_total_xp' => $this->total_xp,
            'new_level' => $newLevel,
            'leveled_up' => $newLevel > $oldLevel,
        ];

        // Dispatch level up event
        if ($newLevel > $oldLevel) {
            event(new \App\Events\LevelUp($this->user, $newLevel, $oldLevel));
        }

        // Dispatch XP earned event
        event(new \App\Events\XPEarned($this->user, $amount, $actionType));

        return $result;
    }

    /**
     * Update daily streak
     */
    public function updateStreak(): void
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        if ($this->last_activity_date === $yesterday) {
            // Continue streak
            $this->current_streak += 1;
            if ($this->current_streak > $this->longest_streak) {
                $this->longest_streak = $this->current_streak;
            }
        } elseif ($this->last_activity_date !== $today) {
            // Reset streak
            $this->current_streak = 1;
            $this->streak_start_date = $today;
        }
    }

    /**
     * Get user rank based on total XP
     */
    public static function getUserRank(int $userId): int
    {
        return self::where('total_xp', '>', self::where('user_id', $userId)->value('total_xp'))
            ->count() + 1;
    }

    /**
     * Get leaderboard (top users by XP)
     */
    public static function getLeaderboard(int $limit = 10): array
    {
        $users = self::with('user:id,name,email,avatar')
            ->orderByDesc('total_xp')
            ->limit($limit)
            ->get();
        
        $result = [];
        foreach ($users as $index => $xp) {
            $result[] = [
                'rank' => $index + 1,
                'user_id' => $xp->user_id,
                'name' => $xp->user ? $xp->user->name : 'Unknown',
                'avatar' => $xp->user ? $xp->user->avatar : null,
                'total_xp' => $xp->total_xp,
                'current_level' => $xp->current_level,
            ];
        }
        
        return $result;
    }

    /**
     * Check if user has achieved a milestone
     */
    public function checkMilestone(string $milestone): bool
    {
        $milestones = [
            'first_task' => $this->tasks_completed >= 1,
            'ten_tasks' => $this->tasks_completed >= 10,
            'fifty_tasks' => $this->tasks_completed >= 50,
            'hundred_tasks' => $this->tasks_completed >= 100,
            'first_referral' => $this->referrals_made >= 1,
            'five_referrals' => $this->referrals_made >= 5,
            'ten_referrals' => $this->referrals_made >= 10,
            'streak_7' => $this->current_streak >= 7,
            'streak_30' => $this->current_streak >= 30,
        ];

        return $milestones[$milestone] ?? false;
    }

    /**
     * Get level title for current level
     */
    public function getLevelTitle(): string
    {
        $titles = [
            1 => 'Newcomer',
            2 => 'Beginner',
            3 => 'Learner',
            4 => 'Contributor',
            5 => 'Active',
            6 => 'Regular',
            7 => 'Veteran',
            8 => 'Expert',
            9 => 'Master',
            10 => 'Champion',
            11 => 'Elite',
            12 => 'Legend',
            13 => 'Hero',
            14 => 'Superstar',
            15 => 'Diamond',
            16 => 'Platinum',
            17 => 'Gold',
            18 => 'Silver',
            19 => 'Bronze',
            20 => 'Ultimate',
        ];

        return $titles[$this->current_level] ?? 'Level ' . $this->current_level;
    }

    /**
     * Get or create XP record for user
     */
    public static function getOrCreateForUser(int $userId): self
    {
        $xp = self::where('user_id', $userId)->first();

        if (!$xp) {
            $xp = self::create([
                'user_id' => $userId,
                'total_xp' => 0,
                'current_level' => 1,
                'xp_for_current_level' => 0,
                'xp_for_next_level' => self::getXPForLevel(2),
            ]);
        }

        return $xp;
    }

    /**
     * Increment task completed count
     */
    public function incrementTasksCompleted(): void
    {
        $this->tasks_completed += 1;
        $this->save();
    }

    /**
     * Increment referrals made count
     */
    public function incrementReferralsMade(): void
    {
        $this->referrals_made += 1;
        $this->save();
    }
}
