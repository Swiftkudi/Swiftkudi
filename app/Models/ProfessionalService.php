<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProfessionalService extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category_id',
        'price',
        'delivery_days',
        'revisions_included',
        'portfolio_links',
        'portfolio_images',
        'status',
        'rejection_reason',
        'is_featured',
        'featured_until',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'portfolio_links' => 'array',
        'portfolio_images' => 'array',
        'is_featured' => 'boolean',
        'featured_until' => 'integer',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DELETED = 'deleted';

    /**
     * Get the user (seller) that owns this service
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the category of this service
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProfessionalServiceCategory::class, 'category_id');
    }

    /**
     * Get add-ons for this service
     */
    public function addons(): HasMany
    {
        return $this->hasMany(ProfessionalServiceAddon::class, 'service_id');
    }

    /**
     * Get orders for this service
     */
    public function orders(): HasMany
    {
        return $this->hasMany(ProfessionalServiceOrder::class, 'service_id');
    }

    /**
     * Get active orders count
     */
    public function activeOrdersCount(): int
    {
        return $this->orders()
            ->whereIn('status', ['paid', 'in_progress', 'delivered', 'revision'])
            ->count();
    }

    /**
     * Check if service is available for new orders
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get the commission rate from settings
     */
    public static function getCommissionRate(): float
    {
        return (float) SystemSetting::get('professional_service_commission', 10); // Default 10%
    }

    /**
     * Calculate platform commission
     */
    public function calculateCommission(float $amount): float
    {
        $rate = self::getCommissionRate();
        return round($amount * ($rate / 100), 2);
    }

    /**
     * Scope a query to only include active services
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include services by category
     */
    public function scopeOfCategory($query, $categoryId)
    {
        if (is_string($categoryId)) {
            // If it's a slug, join with categories table
            return $query->whereHas('category', function($q) use ($categoryId) {
                $q->where('slug', $categoryId);
            });
        }
        return $query->where('category_id', (int) $categoryId);
    }

    /**
     * Scope a query to search by title/description
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
