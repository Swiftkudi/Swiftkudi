<?php

namespace App\Services\Marketplace;

use App\Models\Marketplace\MarketplaceFavourite;
use App\Models\Marketplace\MarketplaceListing;
use App\Models\User;

class FavouriteService
{
    public function toggle(MarketplaceListing $listing, User $user): array
    {
        $existing = MarketplaceFavourite::where('user_id', $user->id)
            ->where('listing_id', $listing->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $listing->decrement('favourites_count');
            return ['favourited' => false];
        }

        MarketplaceFavourite::create(['user_id' => $user->id, 'listing_id' => $listing->id]);
        $listing->increment('favourites_count');
        return ['favourited' => true];
    }

    public function isFavourited(MarketplaceListing $listing, User $user): bool
    {
        return MarketplaceFavourite::where('user_id', $user->id)
            ->where('listing_id', $listing->id)
            ->exists();
    }

    public function getUserFavourites(User $user)
    {
        return MarketplaceFavourite::where('user_id', $user->id)
            ->with('listing')
            ->latest()
            ->paginate(20);
    }
}