<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBoost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'target_type',
        'target_id',
        'started_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(BoostPackage::class, 'package_id');
    }

    public function target()
    {
        return $this->morphTo();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function deactivate()
    {
        $this->is_active = false;
        $this->save();
    }
}
