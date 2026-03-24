<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalServiceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reviewer_id',
        'reviewee_id',
        'rating',
        'comment',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'responded_at' => 'datetime',
    ];

    /**
     * Get the order this review is for
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ProfessionalServiceOrder::class, 'order_id');
    }

    /**
     * Get the reviewer (buyer)
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the reviewee (seller)
     */
    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    /**
     * Update seller's average rating
     */
    public function updateSellerRating(): void
    {
        $reviews = self::where('reviewee_id', $this->reviewee_id)->get();
        
        if ($reviews->isNotEmpty()) {
            $avgRating = $reviews->avg('rating');
            $profile = ServiceProviderProfile::where('user_id', $this->reviewee_id)->first();
            
            if ($profile) {
                $profile->update([
                    'average_rating' => round($avgRating, 2),
                    'total_reviews' => $reviews->count(),
                ]);
            }
        }
    }
}
