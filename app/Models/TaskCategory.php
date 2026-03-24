<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'platform',
        'base_price',
        'min_price',
        'max_price',
        'platform_margin',
        'task_type',
        'proof_type',
        'min_level',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'platform_margin' => 'decimal:2',
        'min_level' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Task types
     */
    public const TYPE_MICRO = 'micro';
    public const TYPE_UGC = 'ugc';
    public const TYPE_REFERRAL = 'referral';
    public const TYPE_PREMIUM = 'premium';

    /**
     * Platform categories
     */
    public const PLATFORM_INSTAGRAM = 'instagram';
    public const PLATFORM_TIKTOK = 'tiktok';
    public const PLATFORM_TWITTER = 'twitter';
    public const PLATFORM_YOUTUBE = 'youtube';
    public const PLATFORM_FACEBOOK = 'facebook';
    public const PLATFORM_GENERAL = 'general';

    /**
     * All categories with full configuration
     */
    public const ALL_CATEGORIES = [
        // === MICRO SOCIAL MEDIA TASKS ===
        [
            'name' => 'Instagram Like',
            'slug' => 'instagram-like',
            'platform' => self::PLATFORM_INSTAGRAM,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 40,
            'min_price' => 30,
            'max_price' => 50,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'instagram',
            'description' => 'Like Instagram posts or comments',
        ],
        [
            'name' => 'Instagram Comment',
            'slug' => 'instagram-comment',
            'platform' => self::PLATFORM_INSTAGRAM,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 40,
            'min_price' => 30,
            'max_price' => 50,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'instagram',
            'description' => 'Comment on Instagram posts',
        ],
        [
            'name' => 'Instagram Follow',
            'slug' => 'instagram-follow',
            'platform' => self::PLATFORM_INSTAGRAM,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 100,
            'min_price' => 100,
            'max_price' => 100,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'instagram',
            'description' => 'Follow Instagram accounts',
        ],
        [
            'name' => 'TikTok Like',
            'slug' => 'tiktok-like',
            'platform' => self::PLATFORM_TIKTOK,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 50,
            'min_price' => 30,
            'max_price' => 100,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'tiktok',
            'description' => 'Like TikTok videos',
        ],
        [
            'name' => 'TikTok Share',
            'slug' => 'tiktok-share',
            'platform' => self::PLATFORM_TIKTOK,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 75,
            'min_price' => 50,
            'max_price' => 100,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'tiktok',
            'description' => 'Share TikTok videos',
        ],
        [
            'name' => 'TikTok Follow',
            'slug' => 'tiktok-follow',
            'platform' => self::PLATFORM_TIKTOK,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 100,
            'min_price' => 100,
            'max_price' => 100,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'tiktok',
            'description' => 'Follow TikTok accounts',
        ],
        [
            'name' => 'Twitter/X Follow',
            'slug' => 'twitter-follow',
            'platform' => self::PLATFORM_TWITTER,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 100,
            'min_price' => 50,
            'max_price' => 150,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'twitter',
            'description' => 'Follow Twitter/X accounts',
        ],
        [
            'name' => 'Twitter/X Retweet',
            'slug' => 'twitter-retweet',
            'platform' => self::PLATFORM_TWITTER,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 100,
            'min_price' => 50,
            'max_price' => 150,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'twitter',
            'description' => 'Retweet Twitter/X posts',
        ],
        [
            'name' => 'YouTube Subscribe',
            'slug' => 'youtube-subscribe',
            'platform' => self::PLATFORM_YOUTUBE,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 200,
            'min_price' => 150,
            'max_price' => 250,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'youtube',
            'description' => 'Subscribe to YouTube channels',
        ],
        [
            'name' => 'YouTube Watch',
            'slug' => 'youtube-watch',
            'platform' => self::PLATFORM_YOUTUBE,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 200,
            'min_price' => 150,
            'max_price' => 250,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'youtube',
            'description' => 'Watch YouTube videos (minimum duration)',
        ],
        [
            'name' => 'Facebook Like',
            'slug' => 'facebook-like',
            'platform' => self::PLATFORM_FACEBOOK,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 75,
            'min_price' => 50,
            'max_price' => 100,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'facebook',
            'description' => 'Like Facebook posts or pages',
        ],
        [
            'name' => 'Facebook Share',
            'slug' => 'facebook-share',
            'platform' => self::PLATFORM_FACEBOOK,
            'task_type' => self::TYPE_MICRO,
            'base_price' => 75,
            'min_price' => 50,
            'max_price' => 100,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'facebook',
            'description' => 'Share Facebook posts',
        ],

        // === UGC / HIGH-VALUE TASKS ===
        [
            'name' => 'Video Testimonial',
            'slug' => 'video-testimonial',
            'platform' => self::PLATFORM_GENERAL,
            'task_type' => self::TYPE_UGC,
            'base_price' => 3500,
            'min_price' => 2500,
            'max_price' => 5000,
            'platform_margin' => 25,
            'proof_type' => 'video',
            'icon' => 'video',
            'description' => 'Record a video testimonial or review',
        ],
        [
            'name' => 'TikTok Product Video',
            'slug' => 'tiktok-product-video',
            'platform' => self::PLATFORM_TIKTOK,
            'task_type' => self::TYPE_UGC,
            'base_price' => 4000,
            'min_price' => 3000,
            'max_price' => 5000,
            'platform_margin' => 25,
            'proof_type' => 'video',
            'icon' => 'tiktok',
            'description' => 'Create and post a TikTok video using/featuring a product',
        ],
        [
            'name' => 'Instagram Story',
            'slug' => 'instagram-story',
            'platform' => self::PLATFORM_INSTAGRAM,
            'task_type' => self::TYPE_UGC,
            'base_price' => 3000,
            'min_price' => 2500,
            'max_price' => 4000,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'instagram',
            'description' => 'Post an Instagram Story featuring a product or brand',
        ],
        [
            'name' => 'Affiliate Product Promo',
            'slug' => 'affiliate-promo',
            'platform' => self::PLATFORM_GENERAL,
            'task_type' => self::TYPE_UGC,
            'base_price' => 3500,
            'min_price' => 2500,
            'max_price' => 5000,
            'platform_margin' => 25,
            'proof_type' => 'link',
            'icon' => 'affiliate',
            'description' => 'Promote affiliate products with unique tracking link',
        ],

        // === REFERRAL / GROWTH TASKS ===
        [
            'name' => 'Invite 3 Users',
            'slug' => 'invite-users',
            'platform' => self::PLATFORM_GENERAL,
            'task_type' => self::TYPE_REFERRAL,
            'base_price' => 150,
            'min_price' => 150,
            'max_price' => 150,
            'platform_margin' => 25,
            'proof_type' => 'link',
            'icon' => 'referral',
            'description' => 'Invite 3 new users who activate their accounts',
        ],
        [
            'name' => 'Join Telegram',
            'slug' => 'join-telegram',
            'platform' => self::PLATFORM_GENERAL,
            'task_type' => self::TYPE_REFERRAL,
            'base_price' => 100,
            'min_price' => 100,
            'max_price' => 100,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'telegram',
            'description' => 'Join Telegram group/channel',
        ],
        [
            'name' => 'Join Discord',
            'slug' => 'join-discord',
            'platform' => self::PLATFORM_GENERAL,
            'task_type' => self::TYPE_REFERRAL,
            'base_price' => 100,
            'min_price' => 100,
            'max_price' => 100,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'discord',
            'description' => 'Join Discord server',
        ],

        // === PREMIUM TASKS (Level 2+) ===
        [
            'name' => 'Affiliate Signup',
            'slug' => 'affiliate-signup',
            'platform' => self::PLATFORM_GENERAL,
            'task_type' => self::TYPE_PREMIUM,
            'base_price' => 500,
            'min_price' => 500,
            'max_price' => 500,
            'platform_margin' => 25,
            'proof_type' => 'screenshot',
            'icon' => 'affiliate',
            'min_level' => 2,
            'description' => 'Sign up for an affiliate program',
        ],
        [
            'name' => 'Product Review Video',
            'slug' => 'product-review',
            'platform' => self::PLATFORM_GENERAL,
            'task_type' => self::TYPE_PREMIUM,
            'base_price' => 500,
            'min_price' => 500,
            'max_price' => 500,
            'platform_margin' => 25,
            'proof_type' => 'video',
            'icon' => 'review',
            'min_level' => 2,
            'description' => 'Create a detailed product review video',
        ],
        [
            'name' => 'Influencer Micro-Campaign',
            'slug' => 'influencer-campaign',
            'platform' => self::PLATFORM_GENERAL,
            'task_type' => self::TYPE_PREMIUM,
            'base_price' => 700,
            'min_price' => 700,
            'max_price' => 700,
            'platform_margin' => 25,
            'proof_type' => 'link',
            'icon' => 'campaign',
            'min_level' => 2,
            'description' => 'Run a micro-influencer campaign across platforms',
        ],
    ];

    /**
     * Relationship: Tasks
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Relationship: Task bundles
     */
    public function bundles()
    {
        return $this->belongsToMany(TaskBundle::class, 'task_bundle_categories');
    }

    /**
     * Calculate worker reward for a task in this category
     */
    public function calculateWorkerReward(float $budget, int $quantity): float
    {
        $totalReward = ($budget * (100 - $this->platform_margin)) / 100;
        return round($totalReward / $quantity, 2);
    }

    /**
     * Calculate platform commission for a task in this category
     */
    public function calculateCommission(float $budget): float
    {
        return round(($budget * $this->platform_margin) / 100, 2);
    }

    /**
     * Get minimum budget for tasks in this category
     */
    public function getMinimumBudgetAttribute(): float
    {
        return $this->min_price ?? $this->base_price;
    }

    /**
     * Get maximum budget for tasks in this category
     */
    public function getMaximumBudgetAttribute(): float
    {
        return $this->max_price ?? $this->base_price;
    }

    /**
     * Check if category is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if this is a premium category (requires level)
     */
    public function isPremium(): bool
    {
        return $this->min_level > 1;
    }

    /**
     * Get icon class for display
     */
    public function getIconClassAttribute(): string
    {
        $icons = [
            'instagram' => 'fab fa-instagram',
            'twitter' => 'fab fa-twitter',
            'youtube' => 'fab fa-youtube',
            'tiktok' => 'fab fa-tiktok',
            'facebook' => 'fab fa-facebook',
            'telegram' => 'fab fa-telegram',
            'discord' => 'fab fa-discord',
            'referral' => 'fas fa-users',
            'ugc' => 'fas fa-video',
            'premium' => 'fas fa-star',
            'micro' => 'fas fa-tasks',
            'video' => 'fas fa-video',
            'affiliate' => 'fas fa-link',
            'campaign' => 'fas fa-bullhorn',
            'review' => 'fas fa-star-half-alt',
        ];
        
        return $icons[$this->icon] ?? 'fas fa-circle';
    }

    /**
     * Get formatted price range
     */
    public function getFormattedPriceRangeAttribute(): string
    {
        if ($this->min_price && $this->max_price && $this->min_price !== $this->max_price) {
            return '₦' . number_format($this->min_price) . ' - ₦' . number_format($this->max_price);
        }
        return '₦' . number_format($this->base_price);
    }

    /**
     * Get all active categories
     */
    public static function getActiveCategories()
    {
        return self::where('is_active', true)->get();
    }

    /**
     * Get categories by task type
     */
    public static function getByType(string $type)
    {
        return self::where('task_type', $type)->where('is_active', true)->get();
    }

    /**
     * Get categories by platform
     */
    public static function getByPlatform(string $platform)
    {
        return self::where('platform', $platform)->where('is_active', true)->get();
    }

    /**
     * Get categories accessible to a user based on level
     */
    public static function getAvailableForUser(User $user)
    {
        $query = self::where('is_active', true);

        // Premium categories require minimum level
        if ($user->level < 2) {
            $query->where(function ($q) {
                $q->whereNull('min_level')
                  ->orWhere('min_level', '<', 2);
            });
        }

        return $query->get();
    }

    /**
     * Seed all categories from configuration
     */
    public static function seedAll()
    {
        foreach (self::ALL_CATEGORIES as $category) {
            self::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
