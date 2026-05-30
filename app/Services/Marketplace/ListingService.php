<?php

namespace App\Services\Marketplace;

use App\Models\Marketplace\MarketplaceListing;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class ListingService
{
    public function createListing(User $seller, array $data): MarketplaceListing
    {
        return DB::transaction(function () use ($seller, $data) {
            $listing = MarketplaceListing::create([
                'user_id' => $seller->id,
                'category_id' => $data['category_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price'],
                'condition' => $data['condition'] ?? 'good',
                'negotiable' => $data['negotiable'] ?? false,
                'images' => $data['images'] ?? [],
                'thumbnail' => $data['thumbnail'] ?? null,
                'tags' => $data['tags'] ?? [],
                'location' => $data['location'] ?? null,
                'available_for_shipping' => $data['available_for_shipping'] ?? false,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'status' => $data['status'] ?? MarketplaceListing::STATUS_DRAFT,
                'is_active' => $data['is_active'] ?? false,
            ]);

            Log::info('Marketplace listing created', [
                'listing_id' => $listing->id,
                'seller_id' => $seller->id,
                'title' => $listing->title,
            ]);

            return $listing;
        });
    }

    public function updateListing(MarketplaceListing $listing, array $data): bool
    {
        return DB::transaction(function () use ($listing, $data) {
            $updateData = [];
            $fillable = ['title', 'description', 'price', 'condition', 'category_id', 'negotiable',
                'thumbnail', 'tags', 'location', 'available_for_shipping',
                'shipping_cost', 'status', 'is_active'];

            foreach ($fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (isset($data['title']) && $data['title'] !== $listing->title) {
                $updateData['slug'] = MarketplaceListing::generateUniqueSlug($data['title']);
            }

            $listing->update($updateData);
            return true;
        });
    }

    public function publish(MarketplaceListing $listing): bool
    {
        $listing->update([
            'status' => MarketplaceListing::STATUS_ACTIVE,
            'is_active' => true,
        ]);
        return true;
    }

    public function unpublish(MarketplaceListing $listing): bool
    {
        $listing->update(['status' => MarketplaceListing::STATUS_DRAFT, 'is_active' => false]);
        return true;
    }

    public function markAsSold(MarketplaceListing $listing): bool
    {
        return DB::transaction(function () use ($listing) {
            $listing->update(['status' => MarketplaceListing::STATUS_SOLD, 'sold_at' => now()]);
            return true;
        });
    }

    public function toggleFeatured(MarketplaceListing $listing): bool
    {
        $listing->update(['is_featured' => !$listing->is_featured]);
        return true;
    }

    public function search(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = MarketplaceListing::query()
            ->with(['category', 'seller'])
            ->withCount('favourites')
            ->where('status', MarketplaceListing::STATUS_ACTIVE)
            ->where('is_active', true);

        if (!empty($filters['q'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['q'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['q'] . '%')
                  ->orWhereJsonContains('tags', $filters['q']);
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (!empty($filters['condition'])) {
            if (is_array($filters['condition'])) {
                $query->whereIn('condition', $filters['condition']);
            } else {
                $query->where('condition', $filters['condition']);
            }
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['university_id'])) {
            $query->where('user_id', function ($q) use ($filters) {
                $q->select('id')->from('users')
                  ->where('university_id', $filters['university_id']);
            });
        }

        if (!empty($filters['shipping'])) {
            $query->where('available_for_shipping', true);
        }

        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('favourites_count', 'desc');
                break;
            case 'featured':
                $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                break;
            default:
                $query->latest();
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function getSellerListings(User $seller, string $status = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = MarketplaceListing::where('user_id', $seller->id)->latest();
        if ($status) {
            $query->where('status', $status);
        }
        return $query->paginate(20);
    }

    public function trackView(MarketplaceListing $listing): void
    {
        $listing->increment('views_count');
    }
}