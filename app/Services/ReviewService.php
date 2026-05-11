<?php

namespace App\Services;

use App\Models\ServiceReview;
use App\Models\ProfessionalServiceReview;
use App\Models\DigitalProductReview;
use App\Models\GrowthListingReview;
use App\Models\SystemSetting;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReviewService
{
    /**
     * Check if reviews are enabled for a specific type
     */
    public function isEnabledFor(string $type): bool
    {
        return match($type) {
            'service', 'services' => SystemSetting::isReviewEnabledForServices(),
            'growth' => SystemSetting::isReviewEnabledForGrowth(),
            'digital', 'product', 'products' => SystemSetting::isReviewEnabledForDigital(),
            default => true,
        };
    }

    /**
     * Validate review data
     */
    public function validateReview(array $data): array
    {
        $errors = [];

        if (!SystemSetting::isReviewEnabledForServices() && 
            !SystemSetting::isReviewEnabledForGrowth() && 
            !SystemSetting::isReviewEnabledForDigital()) {
            $errors[] = 'Reviews are currently disabled.';
        }

        $minRating = SystemSetting::getReviewMinRating();
        $maxRating = SystemSetting::getReviewMaxRating();

        if (isset($data['rating'])) {
            if ($data['rating'] < $minRating || $data['rating'] > $maxRating) {
                $errors[] = "Rating must be between {$minRating} and {$maxRating}.";
            }
        }

        if (isset($data['comment']) && strlen($data['comment']) > 2000) {
            $errors[] = 'Comment cannot exceed 2000 characters.';
        }

        return $errors;
    }

    /**
     * Create a service review (Growth services)
     */
    public function createServiceReview(int $orderId, int $reviewerId, int $reviewedUserId, int $rating, ?string $comment, string $type = GrowthListingReview::TYPE_BUYER_TO_SELLER): GrowthListingReview
    {
        if (!$this->isEnabledFor('service')) {
            throw new \Exception('Service reviews are disabled.');
        }

        $requirePurchase = SystemSetting::isReviewRequirePurchase();
        if ($requirePurchase) {
            // Verify the user actually purchased from this seller
            // This would need to be validated in the controller before calling this service
        }

        $moderationEnabled = SystemSetting::isReviewModerationEnabled();
        $autoPublish = SystemSetting::isReviewAutoPublish();

        $status = ($moderationEnabled && !$autoPublish) 
            ? GrowthListingReview::STATUS_PENDING 
            : GrowthListingReview::STATUS_APPROVED;

        $review = GrowthListingReview::create([
            'order_id' => $orderId,
            'reviewer_id' => $reviewerId,
            'reviewed_user_id' => $reviewedUserId,
            'rating' => $rating,
            'comment' => $comment,
            'type' => $type,
            'status' => $status,
        ]);

        // Update seller rating if auto-publish
        if ($status === GrowthListingReview::STATUS_APPROVED) {
            $review->updateSellerRating();
        }

        // Trigger notification
        $this->notifyReviewCreated($review);

        return $review;
    }

    /**
     * Create a professional service review
     */
    public function createProfessionalServiceReview(int $orderId, int $reviewerId, int $revieweeId, int $rating, ?string $comment): ProfessionalServiceReview
    {
        if (!$this->isEnabledFor('services')) {
            throw new \Exception('Professional service reviews are disabled.');
        }

        $moderationEnabled = SystemSetting::isReviewModerationEnabled();
        $autoPublish = SystemSetting::isReviewAutoPublish();

        $review = ProfessionalServiceReview::create([
            'order_id' => $orderId,
            'reviewer_id' => $reviewerId,
            'reviewee_id' => $revieweeId,
            'rating' => $rating,
            'comment' => $comment,
        ]);

        // Update seller rating
        $review->updateSellerRating();

        // Trigger notification
        app(NotificationManager::class)->notify(
            NotificationManager::EVENT_REVIEW_SUBMITTED,
            User::find($revieweeId),
            [
                'rating' => $rating,
                'item_title' => 'Professional Service',
            ]
        );

        return $review;
    }

    /**
     * Create a digital product review
     */
    public function createDigitalProductReview(int $productId, int $userId, int $rating, ?string $comment, ?array $attachments = []): DigitalProductReview
    {
        if (!$this->isEnabledFor('digital')) {
            throw new \Exception('Digital product reviews are disabled.');
        }

        $moderationEnabled = SystemSetting::isReviewModerationEnabled();
        $autoPublish = SystemSetting::isReviewAutoPublish();

        $review = DigitalProductReview::create([
            'product_id' => $productId,
            'user_id' => $userId,
            'rating' => $rating,
            'comment' => $comment,
            'attachments' => $attachments,
            'is_verified_purchase' => true, // Would need to verify actual purchase
        ]);

        // Update product rating
        $this->updateProductRating($productId);

        // Trigger notification
        $product = \App\Models\DigitalProduct::find($productId);
        if ($product) {
            app(NotificationManager::class)->notify(
                NotificationManager::EVENT_REVIEW_SUBMITTED,
                $product->user,
                [
                    'rating' => $rating,
                    'item_title' => $product->title,
                ]
            );
        }

        return $review;
    }

    /**
     * Update an existing review
     */
    public function updateReview(int $reviewId, string $reviewType, array $data): mixed
    {
        $review = match($reviewType) {
            'service' => GrowthListingReview::find($reviewId),
            'professional' => ProfessionalServiceReview::find($reviewId),
            'digital' => DigitalProductReview::find($reviewId),
            default => null,
        };

        if (!$review) {
            throw new \Exception('Review not found.');
        }

        // Check if review can be edited
        if (method_exists($review, 'canEdit') && !$review->canEdit()) {
            throw new \Exception('Review edit window has expired.');
        }

        if (isset($data['rating'])) {
            $review->rating = $data['rating'];
        }
        if (isset($data['comment'])) {
            $review->comment = $data['comment'];
        }
        $review->save();

        // Update ratings
        if (method_exists($review, 'updateSellerRating')) {
            $review->updateSellerRating();
        } elseif ($reviewType === 'digital') {
            $this->updateProductRating($review->product_id);
        }

        return $review;
    }

    /**
     * Approve a pending review (for moderation)
     */
    public function approveReview(int $reviewId, string $reviewType): mixed
    {
        $review = match($reviewType) {
            'service' => GrowthListingReview::find($reviewId),
            'professional' => ProfessionalServiceReview::find($reviewId),
            default => null,
        };

        if (!$review) {
            throw new \Exception('Review not found.');
        }

        if (method_exists($review, 'approve')) {
            $review->approve();
        }

        // Notify the seller
        app(NotificationManager::class)->notify(
            NotificationManager::EVENT_REVIEW_APPROVED,
            $review->reviewedUser ?? $review->reviewee,
            ['item_title' => 'Your service']
        );

        return $review;
    }

    /**
     * Reject a review
     */
    public function rejectReview(int $reviewId, string $reviewType, string $reason = ''): mixed
    {
        $review = match($reviewType) {
            'service' => GrowthListingReview::find($reviewId),
            default => null,
        };

        if (!$review) {
            throw new \Exception('Review not found.');
        }

        if (method_exists($review, 'reject')) {
            $review->reject();
        }

        // Notify the reviewer
        app(NotificationManager::class)->notify(
            NotificationManager::EVENT_REVIEW_REJECTED,
            $review->reviewer,
            [
                'item_title' => 'Your review',
                'reason' => $reason,
            ]
        );

        return $review;
    }

    /**
     * Get average rating for a user (seller)
     */
    public function getUserAverageRating(int $userId): float
    {
        $serviceReviews = GrowthListingReview::where('reviewed_user_id', $userId)
            ->where('status', GrowthListingReview::STATUS_APPROVED)
            ->get();

        $professionalReviews = ProfessionalServiceReview::where('reviewee_id', $userId)->get();

        $allReviews = $serviceReviews->concat($professionalReviews);

        if ($allReviews->isEmpty()) {
            return 0;
        }

        return round($allReviews->avg('rating'), 2);
    }

    /**
     * Get review counts for a user
     */
    public function getUserReviewCounts(int $userId): array
    {
        return [
            'service' => GrowthListingReview::where('reviewed_user_id', $userId)
                ->where('status', GrowthListingReview::STATUS_APPROVED)
                ->count(),
            'professional' => ProfessionalServiceReview::where('reviewee_id', $userId)->count(),
        ];
    }

    /**
     * Update digital product rating
     */
    protected function updateProductRating(int $productId): void
    {
        $reviews = DigitalProductReview::where('product_id', $productId)->get();

        if ($reviews->isNotEmpty()) {
            $avgRating = $reviews->avg('rating');
            \App\Models\DigitalProduct::where('id', $productId)->update([
                'rating' => round($avgRating, 2),
                'rating_count' => $reviews->count(),
            ]);
        }
    }

    /**
     * Notify about new review creation
     */
    protected function notifyReviewCreated(GrowthListingReview $review): void
    {
        $reviewedUser = $review->reviewedUser;

        if ($reviewedUser) {
            app(NotificationManager::class)->notify(
                NotificationManager::EVENT_REVIEW_SUBMITTED,
                $reviewedUser,
                [
                    'rating' => $review->rating,
                    'item_title' => 'Growth Service',
                ]
            );
        }
    }
}