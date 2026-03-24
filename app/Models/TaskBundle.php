<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'total_price',
        'worker_reward',
        'platform_commission',
        'total_tasks',
        'task_ids',
        'category_ids',
        'difficulty_level',
        'min_level',
        'is_active',
        'expires_at',
        'xp_reward',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'worker_reward' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'task_ids' => 'array',
        'category_ids' => 'array',
        'difficulty_level' => 'string',
        'min_level' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'xp_reward' => 'integer',
    ];

    /**
     * Difficulty levels
     */
    public const DIFFICULTY_EASY = 'easy';
    public const DIFFICULTY_MEDIUM = 'medium';
    public const DIFFICULTY_HARD = 'hard';

    /**
     * Bundle types
     */
    public const TYPE_PLATFORM = 'platform';
    public const TYPE_USER = 'user';

    /**
     * Relationship: User (creator)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Tasks in bundle
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'bundle_tasks');
    }

    /**
     * Relationship: Categories in bundle
     */
    public function categories()
    {
        return $this->belongsToMany(TaskCategory::class, 'task_bundle_categories');
    }

    /**
     * Check if bundle is active
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can perform this bundle
     */
    public function canUserPerform(User $user): bool
    {
        // Check if user is the creator (no self-bundles)
        if ($this->user_id && $this->user_id === $user->id) {
            return false;
        }
        
        // Check if user is activated
        if (!$user->canPerformTasks()) {
            return false;
        }
        
        // Check minimum level
        if ($this->min_level && $user->level < $this->min_level) {
            return false;
        }
        
        return $this->isActive();
    }

    /**
     * Get remaining slots
     */
    public function getRemainingSlotsAttribute(): int
    {
        return $this->total_tasks;
    }

    /**
     * Calculate reward per task in bundle
     */
    public function getRewardPerTaskAttribute(): float
    {
        if ($this->total_tasks == 0) {
            return 0;
        }
        return round($this->worker_reward / $this->total_tasks, 2);
    }

    /**
     * Check if bundle has expired
     */
    public function hasExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get formatted worker reward
     */
    public function getFormattedRewardAttribute(): string
    {
        return '₦' . number_format($this->worker_reward, 2);
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return '₦' . number_format($this->total_price, 2);
    }

    /**
     * Get difficulty badge class
     */
    public function getDifficultyBadgeClassAttribute(): string
    {
        switch ($this->difficulty_level) {
            case self::DIFFICULTY_EASY:
                return 'badge-success';
            case self::DIFFICULTY_MEDIUM:
                return 'badge-warning';
            case self::DIFFICULTY_HARD:
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * XP reward based on difficulty
     */
    public function getXpRewardAttribute(): int
    {
        if ($this->xp_reward) {
            return $this->xp_reward;
        }
        
        switch ($this->difficulty_level) {
            case self::DIFFICULTY_EASY:
                return 10;
            case self::DIFFICULTY_MEDIUM:
                return 25;
            case self::DIFFICULTY_HARD:
                return 50;
            default:
                return 10;
        }
    }

    /**
     * Get formatted XP reward
     */
    public function getFormattedXpRewardAttribute(): string
    {
        return '+' . $this->xp_reward . ' XP';
    }

    /**
     * Check if this is a platform bundle
     */
    public function isPlatformBundle(): bool
    {
        return is_null($this->user_id);
    }

    /**
     * Check if this is a premium bundle (requires level 2+)
     */
    public function isPremium(): bool
    {
        return $this->min_level >= 2;
    }

    /**
     * Get the bundle type
     */
    public function getBundleTypeAttribute(): string
    {
        return $this->isPlatformBundle() ? self::TYPE_PLATFORM : self::TYPE_USER;
    }

    /**
     * Scope: Active bundles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Platform bundles
     */
    public function scopePlatform($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope: User bundles
     */
    public function scopeUserCreated($query, int $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope: By difficulty
     */
    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty_level', $difficulty);
    }

    /**
     * Scope: Premium bundles (level 2+)
     */
    public function scopePremium($query)
    {
        return $query->where('min_level', '>=', 2);
    }

    /**
     * Scope: Available for user
     */
    public function scopeAvailableForUser($query, User $user)
    {
        return $query->active()
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', '!=', $user->id);
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('min_level')
                  ->orWhere('min_level', '<=', $user->level);
            });
    }

    /**
     * Get active bundles
     */
    public static function getActiveBundles()
    {
        return self::active()->get();
    }

    /**
     * Get platform bundles (not user-created)
     */
    public static function getPlatformBundles()
    {
        return self::active()->platform()->get();
    }

    /**
     * Get user bundles
     */
    public static function getUserBundles(int $userId)
    {
        return self::active()->userCreated($userId)->get();
    }

    /**
     * Auto-create bundle from multiple tasks
     */
    public static function createFromTasks(array $taskIds, User $creator, string $name = null): self
    {
        $tasks = Task::whereIn('id', $taskIds)->get();
        
        $totalPrice = $tasks->sum('budget');
        $totalReward = $tasks->sum('worker_reward_per_task');
        $commission = $totalPrice - $totalReward;
        $totalTasks = $tasks->sum('quantity');
        
        // Determine difficulty based on average reward
        $avgReward = $totalReward / max($totalTasks, 1);
        $difficulty = $avgReward >= 500 ? self::DIFFICULTY_HARD : 
                     ($avgReward >= 200 ? self::DIFFICULTY_MEDIUM : self::DIFFICULTY_EASY);
        
        // Determine minimum level based on tasks
        $minLevel = $tasks->max('min_level') ?? 1;
        
        return self::create([
            'user_id' => $creator->id,
            'name' => $name ?? 'Custom Bundle (' . count($taskIds) . ' tasks)',
            'description' => 'A bundle of ' . count($taskIds) . ' tasks for increased rewards',
            'total_price' => $totalPrice,
            'worker_reward' => $totalReward,
            'platform_commission' => $commission,
            'total_tasks' => $totalTasks,
            'task_ids' => $taskIds,
            'category_ids' => $tasks->pluck('category_id')->unique()->toArray(),
            'difficulty_level' => $difficulty,
            'min_level' => $minLevel,
            'is_active' => true,
        ]);
    }

    /**
     * Create platform bundle for promotion
     */
    public static function createPlatformBundle(string $name, float $totalPrice, int $totalTasks, string $difficulty = self::DIFFICULTY_EASY): self
    {
        $workerReward = $totalPrice * 0.75;
        $commission = $totalPrice * 0.25;
        
        return self::create([
            'user_id' => null, // Platform bundle
            'name' => $name,
            'description' => 'SwiftKudi exclusive task bundle - complete multiple tasks for bigger rewards!',
            'total_price' => $totalPrice,
            'worker_reward' => $workerReward,
            'platform_commission' => $commission,
            'total_tasks' => $totalTasks,
            'task_ids' => [],
            'category_ids' => [],
            'difficulty_level' => $difficulty,
            'min_level' => 1,
            'is_active' => true,
        ]);
    }
}
