<?php

namespace App\Services\Marketplace;

use App\Models\Marketplace\Favourite;
use App\Models\Marketplace\Listing;
use App\Models\User;

class FavouriteService
{
    public function toggle(Listing $listing, User $user): array
    {
        $existing = Favourite::where('user_id', $user->id)
            ->where('listing_id', $listing->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $listing->decrement('favourites_count');
            return ['favourited' => false];
        }

        Favourite::create(['user_id' => $user->id, 'listing_id' => $listing->id]);
        $listing->increment('favourites_count');
        return ['favourited' => true];
    }

    public function isFavourited(Listing $listing, User $user): bool
    {
        return Favourite::where('user_id', $user->id)
            ->where('listing_id', $listing->id)
            ->exists();
    }

    public function getUserFavourites(User $user)
    {
        return Favourite::where('user_id', $user->id)
            ->with('listing')
            ->latest()
            ->paginate(20);
    }
}