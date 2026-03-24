<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type', // task, professional, growth, digital_product, job
        'icon',
        'color',
        'parent_id',
        'is_active',
        'order',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(MarketplaceCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MarketplaceCategory::class, 'parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
