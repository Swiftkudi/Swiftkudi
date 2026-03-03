<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'platform',
        'task_type',
        'target_url',
        'target_account',
        'hashtag',
        'proof_instructions',
        'proof_type',
        'budget',
        'quantity',
        'worker_reward_per_task',
        'platform_commission',
        'escrow_amount',
        'min_followers',
        'min_account_age_days',
        'max_submissions_per_user',
        'min_level',
        'is_active',
        'is_approved',
        'is_featured',
        'completed_count',
        'starts_at',
        'expires_at',
        'is_sample',
        'is_permanent_referral',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'worker_reward_per_task' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'escrow_amount' => 'decimal:2',
        'min_followers' => 'integer',
        'min_account_age_days' => 'integer',
        'max_submissions_per_user' => 'integer',
        'min_level' => 'integer',
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
        'completed_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_sample' => 'boolean',
        'is_permanent_referral' => 'boolean',
    ];

    /**
     * Proof types
     */
    public const PROOF_SCREENSHOT = 'screenshot';
    public const PROOF_VIDEO = 'video';
    public const PROOF_AUDIO = 'audio';
    public const PROOF_LINK = 'link';

    /**
     * Proof types list
     */
    public const PROOF_TYPES = [
        self::PROOF_SCREENSHOT => 'Screenshot',
        self::PROOF_VIDEO => 'Video',
        self::PROOF_AUDIO => 'Audio',
        self::PROOF_LINK => 'Link/URL',
    ];

    /**
     * Platforms
     */
    public const PLATFORM_INSTAGRAM = 'instagram';
    public const PLATFORM_TWITTER = 'twitter';
    public const PLATFORM_TIKTOK = 'tiktok';
    public const PLATFORM_YOUTUBE = 'youtube';
    public const PLATFORM_FACEBOOK = 'facebook';
    public const PLATFORM_LINKEDIN = 'linkedin';
    public const PLATFORM_TELEGRAM = 'telegram';
    public const PLATFORM_DISCORD = 'discord';
    public const PLATFORM_OTHER = 'other';

    /**
     * Platform list
     */
    public const PLATFORMS = [
        self::PLATFORM_INSTAGRAM => 'Instagram',
        self::PLATFORM_TIKTOK => 'TikTok',
        self::PLATFORM_TWITTER => 'Twitter/X',
        self::PLATFORM_YOUTUBE => 'YouTube',
        self::PLATFORM_FACEBOOK => 'Facebook',
        self::PLATFORM_LINKEDIN => 'LinkedIn',
        self::PLATFORM_TELEGRAM => 'Telegram',
        self::PLATFORM_DISCORD => 'Discord',
        self::PLATFORM_OTHER => 'Other',
    ];

    /**
     * Task types
     */
    public const TYPE_LIKE = 'like';
    public const TYPE_COMMENT = 'comment';
    public const TYPE_FOLLOW = 'follow';
    public const TYPE_SHARE = 'share';
    public const TYPE_SUBSCRIBE = 'subscribe';
    public const TYPE_VIEW = 'view';
    public const TYPE_RETWEET = 'retweet';
    public const TYPE_REVIEW = 'review';
    public const TYPE_TESTIMONIAL = 'testimonial';
    public const TYPE_PROMO_VIDEO = 'promo_video';
    public const TYPE_STORY = 'story';
    public const TYPE_AFFILIATE = 'affiliate';
    public const TYPE_JOIN = 'join';
    public const TYPE_REFERRAL = 'referral';
    public const TYPE_WATCH = 'watch';
    public const TYPE_CAMPAIGN = 'campaign';

    /**
     * Task type groups
     */
    public const TASK_TYPE_GROUPS = [
        'micro' => [
            self::TYPE_LIKE => 'Like',
            self::TYPE_COMMENT => 'Comment',
            self::TYPE_FOLLOW => 'Follow',
            self::TYPE_SHARE => 'Share',
            self::TYPE_VIEW => 'View',
            self::TYPE_RETWEET => 'Retweet',
            self::TYPE_SUBSCRIBE => 'Subscribe',
            self::TYPE_WATCH => 'Watch',
        ],
        'ugc' => [
            self::TYPE_TESTIMONIAL => 'Video Testimonial',
            self::TYPE_REVIEW => 'Product Review',
            self::TYPE_PROMO_VIDEO => 'Promotional Video',
            self::TYPE_STORY => 'Instagram Story',
        ],
        'growth' => [
            self::TYPE_REFERRAL => 'Invite Users',
            self::TYPE_JOIN => 'Join Group',
        ],
        'premium' => [
            self::TYPE_AFFILIATE => 'Affiliate Signup',
            self::TYPE_CAMPAIGN => 'Influencer Campaign',
        ],
    ];

    /**
     * Price ranges by task type
     */
    public const PRICE_RANGES = [
        'instagram_like' => ['min' => 30, 'max' => 50],
        'instagram_follow' => ['min' => 100, 'max' => 100],
        'tiktok_like' => ['min' => 30, 'max' => 100],
        'tiktok_share' => ['min' => 50, 'max' => 100],
        'tiktok_follow' => ['min' => 100, 'max' => 100],
        'twitter_follow' => ['min' => 50, 'max' => 150],
        'twitter_retweet' => ['min' => 50, 'max' => 150],
        'youtube_subscribe' => ['min' => 150, 'max' => 250],
        'youtube_watch' => ['min' => 150, 'max' => 250],
        'facebook_like' => ['min' => 50, 'max' => 100],
        'facebook_share' => ['min' => 50, 'max' => 100],
        'testimonial' => ['min' => 2500, 'max' => 5000],
        'tiktok_product' => ['min' => 3000, 'max' => 5000],
        'instagram_story' => ['min' => 2500, 'max' => 4000],
        'affiliate_promo' => ['min' => 2500, 'max' => 5000],
        'invite_users' => ['min' => 150, 'max' => 150],
        'join_telegram' => ['min' => 100, 'max' => 100],
        'join_discord' => ['min' => 100, 'max' => 100],
        'affiliate_signup' => ['min' => 500, 'max' => 500],
        'product_review' => ['min' => 500, 'max' => 500],
        'influencer_campaign' => ['min' => 700, 'max' => 700],
    ];

    /**
     * Relationship: Creator (Client)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Category
     */
    public function category()
    {
        return $this->belongsTo(TaskCategory::class);
    }

    /**
     * Relationship: Completions
     */
    public function completions()
    {
        return $this->hasMany(TaskCompletion::class);
    }

    /**
     * Relationship: Approved completions only
     */
    public function approvedCompletions()
    {
        return $this->hasMany(TaskCompletion::class)->where('status', TaskCompletion::STATUS_APPROVED);
    }

    /**
     * Unified completed slots accessor used across views.
     */
    public function getCompletedSlotsAttribute(): int
    {
        return $this->task_completions_count;
    }

    /**
     * Unified completion count accessor.
     *
     * Some controllers load `completions_count`, some load
     * `task_completions_count`, and some rely on `completed_count`.
     * This accessor normalizes all paths and falls back to live approved count.
     */
    public function getTaskCompletionsCountAttribute(): int
    {
        if (array_key_exists('task_completions_count', $this->attributes)) {
            return (int) $this->attributes['task_completions_count'];
        }

        if (array_key_exists('completions_count', $this->attributes)) {
            return (int) $this->attributes['completions_count'];
        }

        if (array_key_exists('completed_count', $this->attributes)) {
            return (int) $this->attributes['completed_count'];
        }

        return (int) $this->approvedCompletions()->count();
    }

    /**
     * Relationship: Bundles containing this task
     */
    public function bundles()
    {
        return $this->belongsToMany(TaskBundle::class, 'bundle_tasks');
    }

    /**
     * Check if task is active
     */
    public function isActive(): bool
    {
        if (!$this->is_active || !$this->is_approved) {
            return false;
        }
        
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if task has available slots
     */
    public function hasAvailableSlots(): bool
    {
        return $this->task_completions_count < $this->quantity;
    }

    /**
     * Get remaining slots
     */
    public function getRemainingSlotsAttribute(): int
    {
        return max(0, $this->quantity - $this->task_completions_count);
    }

    /**
     * Check if user can perform this task
     */
    public function canUserPerform(User $user): bool
    {
        // Check if user is the creator (no self-tasks)
        if ($this->user_id === $user->id) {
            return false;
        }
        
        // Check if user is activated
        if (!$user->canPerformTasks()) {
            return false;
        }
        
        // Check if user has already completed
        $existingCompletion = $this->completions()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
        
        if ($existingCompletion) {
            return false;
        }
        
        // Check if task is active
        if (!$this->isActive()) {
            return false;
        }
        
        // Check for available slots
        if (!$this->hasAvailableSlots()) {
            return false;
        }
        
        // Check minimum level for premium tasks
        if ($this->min_level && $user->level < $this->min_level) {
            return false;
        }
        
        // Check minimum followers if set
        if ($this->min_followers && $user->level < 2) {
            // Would need follower count from user profile
        }
        
        return true;
    }

    /**
     * Check if task is a premium task (requires level 2+)
     */
    public function isPremium(): bool
    {
        return $this->min_level >= 2;
    }

    /**
     * Check if task is a micro task
     */
    public function isMicro(): bool
    {
        return in_array($this->task_type, [
            self::TYPE_LIKE, self::TYPE_COMMENT, self::TYPE_FOLLOW,
            self::TYPE_SHARE, self::TYPE_VIEW, self::TYPE_RETWEET, self::TYPE_SUBSCRIBE, self::TYPE_WATCH
        ]);
    }

    /**
     * Calculate rewards and commission
     */
    public static function calculatePricing(float $budget, TaskCategory $category = null): array
    {
        $margin = $category ? $category->platform_margin : 25;
        
        $commission = round(($budget * $margin) / 100, 2);
        $workerReward = $budget - $commission;
        
        return [
            'budget' => $budget,
            'commission' => $commission,
            'worker_reward' => $workerReward,
            'platform_margin' => $margin,
        ];
    }

    /**
     * Calculate worker reward per task
     */
    public static function calculateRewardPerTask(float $budget, int $quantity, TaskCategory $category = null): array
    {
        $pricing = self::calculatePricing($budget, $category);
        
        $rewardPerTask = $quantity > 0 ? round($pricing['worker_reward'] / $quantity, 2) : 0;
        
        // Adjust for rounding
        $totalReward = $rewardPerTask * $quantity;
        $commission = $budget - $totalReward;
        
        return [
            'budget' => $budget,
            'worker_reward_per_task' => $rewardPerTask,
            'platform_commission' => $commission,
            'total_worker_reward' => $totalReward,
            'platform_margin' => $pricing['platform_margin'],
        ];
    }

    /**
     * Get recommended price for a task type
     */
    public static function getRecommendedPrice(string $taskType): ?array
    {
        return self::PRICE_RANGES[$taskType] ?? null;
    }

    /**
     * Get formatted budget
     */
    public function getFormattedBudgetAttribute(): string
    {
        return '₦' . number_format($this->budget, 2);
    }

    /**
     * Get formatted worker reward
     */
    public function getFormattedRewardAttribute(): string
    {
        return '₦' . number_format($this->worker_reward_per_task, 2);
    }

    /**
     * Get formatted platform commission
     */
    public function getFormattedCommissionAttribute(): string
    {
        return '₦' . number_format($this->platform_commission, 2);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->quantity == 0) {
            return 100;
        }
        
        return (int) (($this->task_completions_count / $this->quantity) * 100);
    }

    /**
     * Scope: Active tasks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('is_approved', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: By platform
     */
    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: By task type
     */
    public function scopeByTaskType($query, string $taskType)
    {
        return $query->where('task_type', $taskType);
    }

    /**
     * Scope: Micro tasks only
     */
    public function scopeMicro($query)
    {
        return $query->whereIn('task_type', [
            self::TYPE_LIKE, self::TYPE_COMMENT, self::TYPE_FOLLOW,
            self::TYPE_SHARE, self::TYPE_VIEW, self::TYPE_RETWEET, 
            self::TYPE_SUBSCRIBE, self::TYPE_WATCH
        ]);
    }

    /**
     * Scope: Premium tasks (level 2+)
     */
    public function scopePremium($query)
    {
        return $query->where('min_level', '>=', 2);
    }

    /**
     * Scope: Featured
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Available for user (not completed, active, has slots)
     */
    public function scopeAvailableForUser($query, User $user)
    {
        return $query->active()
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('completions', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->whereIn('status', ['pending', 'approved']);
            });
    }

    /**
     * Scope: Permanent referral tasks (visible to all users)
     */
    public function scopePermanentReferral($query)
    {
        return $query->where('is_permanent_referral', true)
            ->where('is_active', true);
    }

    /**
     * Get platform icon
     */
    public function getPlatformIconAttribute(): string
    {
        $icons = [
            'instagram' => 'fab fa-instagram',
            'twitter' => 'fab fa-twitter',
            'youtube' => 'fab fa-youtube',
            'tiktok' => 'fab fa-tiktok',
            'facebook' => 'fab fa-facebook',
            'linkedin' => 'fab fa-linkedin',
            'telegram' => 'fab fa-telegram',
            'discord' => 'fab fa-discord',
        ];
        
        return $icons[$this->platform] ?? 'fas fa-globe';
    }

    /**
     * Get proof type icon
     */
    public function getProofTypeIconAttribute(): string
    {
        $icons = [
            self::PROOF_SCREENSHOT => 'fas fa-camera',
            self::PROOF_VIDEO => 'fas fa-video',
            self::PROOF_AUDIO => 'fas fa-microphone',
            self::PROOF_LINK => 'fas fa-link',
        ];
        
        return $icons[$this->proof_type] ?? 'fas fa-file';
    }

    /**
     * Get task difficulty level
     */
    public function getDifficultyLevelAttribute(): string
    {
        if ($this->isMicro()) {
            return 'easy';
        }
        
        if ($this->isPremium() || $this->min_level >= 2) {
            return 'hard';
        }
        
        return 'medium';
    }
}
