<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfessionalServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    /**
     * Get services in this category
     */
    public function services(): HasMany
    {
        return $this->hasMany(ProfessionalService::class, 'category_id');
    }

    /**
     * Get active services count
     */
    public function activeServicesCount(): int
    {
        return $this->services()->where('status', ProfessionalService::STATUS_ACTIVE)->count();
    }

    /**
     * Scope to only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
