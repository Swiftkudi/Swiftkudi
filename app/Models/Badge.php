<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'xp_reward',
        'requirements',
    ];

    protected $casts = [
        'xp_reward' => 'integer',
        'requirements' => 'array',
    ];

    /**
     * Relationship: Users who earned this badge
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at')
            ->using(UserBadge::class);
    }

    /**
     * Get formatted XP reward
     */
    public function getFormattedXpRewardAttribute(): string
    {
        return '+' . $this->xp_reward . ' XP';
    }

    /**
     * Get icon URL or class
     */
    public function getIconDisplayAttribute(): string
    {
        return '<i class="fas fa-' . ($this->icon ?? 'medal') . '"></i>';
    }

    /**
     * Check if badge requirements are met
     */
    public function checkRequirements(User $user, array $data = []): bool
    {
        if (empty($this->requirements)) {
            return false;
        }
        
        $requirements = $this->requirements;
        
        foreach ($requirements as $requirement => $value) {
            switch ($requirement) {
                case 'tasks_completed':
                    if ($user->taskCompletions()->where('status', 'approved')->count() < $value) {
                        return false;
                    }
                    break;
                case 'level':
                    if ($user->level < $value) {
                        return false;
                    }
                    break;
                case 'streak':
                    if ($user->daily_streak < $value) {
                        return false;
                    }
                    break;
                case 'referrals':
                    $referralCount = Referral::where('referrer_id', $user->id)
                        ->whereNotNull('activated_at')
                        ->count();
                    if ($referralCount < $value) {
                        return false;
                    }
                    break;
                case 'total_earned':
                    if (!$user->wallet || $user->wallet->total_earned < $value) {
                        return false;
                    }
                    break;
            }
        }
        
        return true;
    }

    /**
     * Award badge to user
     */
    public function awardTo(User $user): bool
    {
        if ($user->badges->contains($this->id)) {
            return false; // Already earned
        }
        
        $user->badges()->attach($this->id, ['earned_at' => now()]);
        
        // Award XP
        if ($this->xp_reward > 0) {
            $user->addExperience($this->xp_reward);
        }
        
        return true;
    }

    /**
     * Get all badges
     */
    public static function getAllBadges()
    {
        return self::all();
    }

    /**
     * Get active badges
     */
    public static function getActiveBadges()
    {
        return self::all(); // All badges are active by default
    }
}
