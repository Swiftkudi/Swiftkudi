<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'platform',
        'task_action',
        'min_price',
        'max_price',
        'base_price',
        'proof_type',
        'description',
        'instructions',
        'icon',
        'min_level_required',
        'is_active',
    ];

    protected $casts = [
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'base_price' => 'decimal:2',
        'min_level_required' => 'integer',
        'is_active' => 'boolean',
    ];

    // Category constants
    const CATEGORY_MICRO = 'micro';
    const CATEGORY_UGC = 'ugc';
    const CATEGORY_REFERRAL = 'referral';
    const CATEGORY_PREMIUM = 'premium';

    const CATEGORIES = [
        self::CATEGORY_MICRO => 'Micro Social Media Tasks',
        self::CATEGORY_UGC => 'UGC / High-Value Tasks',
        self::CATEGORY_REFERRAL => 'Referral / Growth Tasks',
        self::CATEGORY_PREMIUM => 'Premium Tasks',
    ];

    const PLATFORMS = [
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'twitter' => 'Twitter/X',
        'youtube' => 'YouTube',
        'facebook' => 'Facebook',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
        'linkedin' => 'LinkedIn',
        'discord' => 'Discord',
        'general' => 'General',
        'affiliate' => 'Affiliate',
    ];

    const PROOF_TYPES = [
        'screenshot' => 'Screenshot',
        'video' => 'Video',
        'audio' => 'Audio',
        'link' => 'Link/URL',
    ];

    /**
     * Relationship: Tasks of this type
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Scope: Active task types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: By platform
     */
    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope: By minimum level
     */
    public function scopeMinLevel($query, int $level)
    {
        return $query->where('min_level_required', '<=', $level);
    }

    /**
     * Scope: Micro tasks
     */
    public function scopeMicro($query)
    {
        return $query->where('category', self::CATEGORY_MICRO);
    }

    /**
     * Scope: UGC tasks
     */
    public function scopeUgc($query)
    {
        return $query->where('category', self::CATEGORY_UGC);
    }

    /**
     * Scope: Referral tasks
     */
    public function scopeReferral($query)
    {
        return $query->where('category', self::CATEGORY_REFERRAL);
    }

    /**
     * Scope: Premium tasks
     */
    public function scopePremium($query)
    {
        return $query->where('category', self::CATEGORY_PREMIUM);
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get platform label
     */
    public function getPlatformLabelAttribute(): string
    {
        return self::PLATFORMS[$this->platform] ?? $this->platform;
    }

    /**
     * Get proof type label
     */
    public function getProofTypeLabelAttribute(): string
    {
        return self::PROOF_TYPES[$this->proof_type] ?? $this->proof_type;
    }

    /**
     * Get formatted price range
     */
    public function getPriceRangeAttribute(): string
    {
        return '₦' . number_format($this->min_price, 0) . ' - ₦' . number_format($this->max_price, 0);
    }

    /**
     * Get default price (average of min and max)
     */
    public function getDefaultPriceAttribute(): float
    {
        return ($this->min_price + $this->max_price) / 2;
    }

    /**
     * Calculate worker reward for given budget and quantity
     */
    public function calculateWorkerReward(float $budget, int $quantity): float
    {
        if ($quantity <= 0) {
            return 0;
        }
        return round(($budget * 0.75) / $quantity, 2);
    }

    /**
     * Calculate platform commission for given budget
     */
    public function calculatePlatformCommission(float $budget): float
    {
        return round($budget * 0.25, 2);
    }

    /**
     * Check if task type is premium (requires level 2+)
     */
    public function isPremium(): bool
    {
        return $this->category === self::CATEGORY_PREMIUM;
    }

    /**
     * Check if user can access this task type
     */
    public function canUserAccess(User $user): bool
    {
        return $user->level >= $this->min_level_required;
    }

    /**
     * Get task types grouped by category
     */
    public static function getGroupedByCategory(?int $userLevel = null)
    {
        $query = self::active();

        if ($userLevel) {
            $query->minLevel($userLevel);
        }

        return $query->orderBy('category')->orderBy('platform')->get()->groupBy('category');
    }

    /**
     * Get task types grouped by platform
     */
    public static function getGroupedByPlatform(?int $userLevel = null)
    {
        $query = self::active();

        if ($userLevel) {
            $query->minLevel($userLevel);
        }

        return $query->orderBy('platform')->orderBy('name')->get()->groupBy('platform');
    }

    /**
     * Get task types for a specific category
     */
    public static function getByCategory(string $category, ?int $userLevel = null)
    {
        $query = self::active()->byCategory($category);

        if ($userLevel) {
            $query->minLevel($userLevel);
        }

        return $query->orderBy('platform')->orderBy('name')->get();
    }

    /**
     * Get task types for a specific platform
     */
    public static function getByPlatform(string $platform, ?int $userLevel = null)
    {
        $query = self::active()->byPlatform($platform);

        if ($userLevel) {
            $query->minLevel($userLevel);
        }

        return $query->orderBy('category')->orderBy('name')->get();
    }

    /**
     * Find by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)->first();
    }

    /**
     * Get all platforms with task type counts
     */
    public static function getPlatformStats(): array
    {
        return self::active()
            ->selectRaw('platform, COUNT(*) as count, MIN(min_price) as min_price, MAX(max_price) as max_price')
            ->groupBy('platform')
            ->get()
            ->keyBy('platform')
            ->toArray();
    }

    /**
     * Get all categories with task type counts
     */
    public static function getCategoryStats(): array
    {
        return self::active()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->keyBy('category')
            ->toArray();
    }
}
