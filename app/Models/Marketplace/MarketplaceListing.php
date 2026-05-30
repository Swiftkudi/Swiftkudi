<?php

namespace App\Models\Marketplace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MarketplaceListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'description',
        'condition', 'price', 'negotiable', 'images', 'thumbnail',
        'tags', 'location', 'available_for_shipping', 'shipping_cost',
        'is_featured', 'is_active', 'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'negotiable' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'images' => 'array',
        'tags' => 'array',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_REVIEW = 'pending_review';
    const STATUS_ACTIVE = 'active';
    const STATUS_SOLD = 'sold';
    const STATUS_EXPIRED = 'expired';
    const STATUS_FLAGGED = 'flagged';
    const STATUS_REMOVED = 'removed';

    public function seller()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\MarketplaceCategory::class, 'category_id');
    }

    public function orders()
    {
        return $this->hasMany(MarketplaceOrder::class, 'listing_id');
    }

    public function reviews()
    {
        return $this->hasMany(MarketplaceReview::class);
    }

    public function favourites()
    {
        return $this->hasMany(MarketplaceFavourite::class, 'listing_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeNotSold($query)
    {
        return $query->where('status', '!=', self::STATUS_SOLD);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeCondition($query, string $condition)
    {
        return $query->where('condition', $condition);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySeller($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhereJsonContains('tags', $term);
        });
    }

    protected static function booted(): void
    {
        static::creating(function (self $listing) {
            if (empty($listing->slug)) {
                $listing->slug = self::generateUniqueSlug($listing->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = self::whereRaw("slug REGEXP '^{$slug}(-[0-9]+)?$'")->count();
        return $count > 0 ? "{$slug}-{$count}" : $slug;
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function isFavouritedBy(\App\Models\User $user): bool
    {
        return $this->favourites()->where('user_id', $user->id)->exists();
    }

   
}