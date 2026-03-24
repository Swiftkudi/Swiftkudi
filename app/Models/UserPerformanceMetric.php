<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPerformanceMetric extends Model
{
    protected $fillable = [
        'user_id',
        'total_tasks_completed',
        'total_earnings',
        'total_referrals',
        'total_sales',
        'average_rating',
        'total_reviews',
        'consecutive_days_active',
        'last_active_date',
    ];

    protected $casts = [
        'total_earnings' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'last_active_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incrementTasks(int $count = 1): void
    {
        $this->increment('total_tasks_completed', $count);
    }

    public function incrementEarnings(float $amount): void
    {
        $this->increment('total_earnings', $amount);
    }

    public function incrementSales(int $count = 1): void
    {
        $this->increment('total_sales', $count);
    }

    public function incrementReferrals(int $count = 1): void
    {
        $this->increment('total_referrals', $count);
    }

    public function updateRating(float $newRating): void
    {
        $totalReviews = $this->total_reviews + 1;
        $currentTotal = $this->average_rating * $this->total_reviews;
        $newAverage = ($currentTotal + $newRating) / $totalReviews;
        
        $this->average_rating = $newAverage;
        $this->total_reviews = $totalReviews;
        $this->save();
    }

    public function recordActivity(): void
    {
        $today = now()->toDateString();
        
        if ($this->last_active_date) {
            $lastDate = $this->last_active_date->toDateString();
            $yesterday = now()->subDay()->toDateString();
            
            if ($lastDate === $yesterday) {
                $this->increment('consecutive_days_active');
            } elseif ($lastDate !== $today) {
                $this->consecutive_days_active = 1;
            }
        } else {
            $this->consecutive_days_active = 1;
        }
        
        $this->last_active_date = $today;
        $this->save();
    }
}
