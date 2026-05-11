<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowthListingReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reviewer_id',
        'reviewed_user_id',
        'rating',
        'comment',
        'type',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    const TYPE_BUYER_TO_SELLER = 'buyer_to_seller';
    const TYPE_SELLER_TO_BUYER = 'seller_to_buyer';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(GrowthOrder::class, 'order_id');
    }

    /**
     * Get the reviewer
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the reviewed user
     */
    public function reviewedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }

    /**
     * Get the listing through order
     */
    public function listing()
    {
        return $this->hasOneThrough(
            GrowthListing::class,
            GrowthOrder::class,
            'id',
            'id',
            'order_id',
            'listing_id'
        );
    }

    /**
     * Check if review can be edited
     */
    public function canEdit(): bool
    {
        $editWindowHours = SystemSetting::getReviewEditWindowHours(24);
        return $this->created_at->diffInHours(now()) < $editWindowHours;
    }

    /**
     * Check if review is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Approve the review
     */
    public function approve(): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->save();
        $this->updateSellerRating();
    }

    /**
     * Reject the review
     */
    public function reject(): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->save();
    }

    /**
     * Update seller's average rating
     */
    public function updateSellerRating(): void
    {
        $reviews = self::where('reviewed_user_id', $this->reviewed_user_id)
            ->where('status', self::STATUS_APPROVED)
            ->get();

        if ($reviews->isNotEmpty()) {
            $avgRating = $reviews->avg('rating');
            $profile = ServiceProviderProfile::where('user_id', $this->reviewed_user_id)->first();

            if ($profile) {
                $profile->update([
                    'rating_average' => round($avgRating, 2),
                    'review_count' => $reviews->count(),
                ]);
            }

            // Also update growth listing rating
            $listing = $this->listing()->first();
            if ($listing) {
                $listing->update([
                    'rating' => round($avgRating, 2),
                    'review_count' => $reviews->count(),
                ]);
            }
        }
    }
}