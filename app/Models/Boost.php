<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Boost extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'boostable_type',
        'boostable_id',
        'status',
        'amount_paid',
        'started_at',
        'expires_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(BoostPackage::class, 'package_id');
    }

    public function boostable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->expires_at && 
               $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function deactivate(): void
    {
        $this->status = 'expired';
        $this->save();
        
        // Update the boostable item
        if ($this->boostable) {
            $this->boostable->is_featured = false;
            $this->boostable->save();
        }
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('boostable_type', $type);
    }
}
