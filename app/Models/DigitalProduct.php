<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class DigitalProduct extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'sale_price',
        'thumbnail',
        'file_path',
        'file_size',
        'file_type',
        'category_id',
        'tags',
        'downloads',
        'total_sales',
        'rating',
        'rating_count',
        'is_featured',
        'is_active',
        'is_free',
        'license_type',
        'version',
        'changelog',
        'requirements',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'tags' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_free' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketplaceCategory::class, 'category_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(DigitalProductOrder::class, 'product_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(DigitalProductReview::class, 'product_id');
    }

    public function getCurrentPriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (!$this->sale_price || $this->sale_price >= $this->price) {
            return 0;
        }
        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function getTagListAttribute(): array
    {
        $tags = $this->getAttribute('tags');

        if ($tags instanceof Collection) {
            return $tags->map(fn ($tag) => trim((string) $tag))->filter()->values()->all();
        }

        if (is_array($tags)) {
            return array_values(array_filter(array_map(fn ($tag) => trim((string) $tag), $tags)));
        }

        if (!is_string($tags) || trim($tags) === '') {
            return [];
        }

        $decoded = json_decode($tags, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map(fn ($tag) => trim((string) $tag), $decoded)));
        }

        return array_values(array_filter(array_map('trim', explode(',', $tags))));
    }
}
