<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'requirements',
        'benefits',
        'job_type',
        'experience_level',
        'budget_min',
        'budget_max',
        'duration',
        'location',
        'status',
        'expires_at',
        'views_count',
        'applications_count',
        'positions_available',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'expires_at' => 'datetime',
        'views_count' => 'integer',
        'applications_count' => 'integer',
        'positions_available' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketplaceCategory::class, 'category_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function getBudgetRangeAttribute()
    {
        if ($this->budget_min == $this->budget_max) {
            return '₦' . number_format($this->budget_min);
        }
        return '₦' . number_format($this->budget_min) . ' - ₦' . number_format($this->budget_max);
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getTypeLabelAttribute()
    {
        $types = [
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'contract' => 'Contract',
            'internship' => 'Internship',
        ];
        return $types[$this->job_type] ?? ucfirst($this->job_type);
    }

    public function getLevelLabelAttribute()
    {
        $levels = [
            'entry' => 'Entry Level',
            'intermediate' => 'Intermediate',
            'expert' => 'Expert',
        ];
        return $levels[$this->experience_level] ?? ucfirst($this->experience_level);
    }

    public function getHiredCountAttribute()
    {
        return $this->applications()->where('status', 'hired')->count();
    }

    public function getPositionsRemainingAttribute()
    {
        return max(0, $this->positions_available - $this->hired_count);
    }

    public function getIsFullyHiredAttribute()
    {
        return $this->positions_remaining <= 0;
    }

    public function hiredApplications()
    {
        return $this->hasMany(JobApplication::class, 'job_id')->where('status', 'hired');
    }
}
