<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\ListingService;
use App\Services\Marketplace\FavouriteService;
use App\Models\MarketplaceCategory;
use App\Models\Marketplace\MarketplaceListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    protected ListingService $listingService;
    protected FavouriteService $favouriteService;

    public function __construct()
    {
        $this->listingService = app(ListingService::class);
        $this->favouriteService = app(FavouriteService::class);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'category', 'condition', 'min_price', 'max_price', 'university_id', 'shipping', 'sort', 'per_page']);
        $listings = $this->listingService->search($filters);

        $categories = MarketplaceCategory::where('type', 'marketplace')
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        $featuredListings = MarketplaceListing::active()
            ->where('is_featured', true)
            ->with(['seller', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        $campusMatches = null;
        if (Auth::check() && Auth::user()->university_id) {
            $campusMatches = MarketplaceListing::active()
                ->with(['seller', 'category'])
                ->whereHas('seller', function ($query) {
                    $query->where('university_id', Auth::user()->university_id);
                })
                ->latest()
                ->limit(8)
                ->get();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'listings' => $listings->items(),
                'pagination' => [
                    'total' => $listings->total(),
                    'current_page' => $listings->currentPage(),
                    'last_page' => $listings->lastPage(),
                    'next_page_url' => $listings->nextPageUrl(),
                ],
            ]);
        }

        return view('marketplace.listings.index', compact('listings', 'categories', 'featuredListings', 'campusMatches'));
    }

    public function search(Request $request)
    {
        return redirect()->route('marketplace.listings.index', $request->all());
    }

    public function category($slug)
    {
        $category = \App\Models\MarketplaceCategory::where('slug', $slug)
            ->where('type', 'marketplace')
            ->firstOrFail();

        $categories = MarketplaceCategory::where('type', 'marketplace')
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

        $listings = MarketplaceListing::active()
            ->where('category_id', $category->id)
            ->latest()
            ->paginate(20);

        return view('marketplace.listings.index', compact('listings', 'category', 'categories'));
    }

    public function show($id)
    {
        $listing = MarketplaceListing::with(['seller', 'category', 'orders'])
            ->where('id', $id)
            ->withCount('favourites')
            ->firstOrFail();

        if ($listing->status !== MarketplaceListing::STATUS_ACTIVE) {
            abort(404);
        }

        $listing->increment('views_count');

        $isFavourited = false;
        if (Auth::check()) {
            $isFavourited = $this->favouriteService->isFavourited($listing, Auth::user());
        }

        $similar = MarketplaceListing::active()
            ->where('category_id', $listing->category_id)
            ->where('id', '!=', $listing->id)
            ->limit(6)
            ->get();

        $canPurchase = Auth::check()
            && Auth::id() !== $listing->user_id
            && Auth::user()->wallet !== null;

        return view('marketplace.listings.show', compact(
            'listing', 'isFavourited', 'similar', 'canPurchase'
        ));
    }

    public function create()
    {
        $categories = \App\Models\MarketplaceCategory::where('type', 'marketplace')
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        return view('marketplace.listings.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:20|max:5000',
            'category_id' => 'nullable|exists:marketplace_categories,id',
            'condition' => 'required|in:new,like_new,good,fair,used',
            'price' => 'required|numeric|min:0|max:9999999.99',
            'negotiable' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'available_for_shipping' => 'nullable|boolean',
            'shipping_cost' => 'nullable|numeric|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'tags_text' => 'nullable|string|max:1000',
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $images[] = $file->store('marketplace/listings/' . Auth::id(), 'public');
            }
            $validated['images'] = $images;
            $validated['thumbnail'] = $images[0] ?? null;
        }

        if (array_key_exists('tags_text', $validated)) {
            $validated['tags'] = array_values(array_filter(array_map('trim', explode(',', $validated['tags_text']))));
        }

        if ($request->input('publish')) {
            $validated['status'] = MarketplaceListing::STATUS_PENDING_REVIEW;
            $validated['is_active'] = false;
        }

        $listing = $this->listingService->createListing(Auth::user(), $validated);

        return redirect()->route('marketplace.listings.show', $listing->id)
            ->with('success', 'Listing created successfully!');
    }

    public function edit($id)
    {
        $listing = MarketplaceListing::where('user_id', Auth::id())->findOrFail($id);

        if (in_array($listing->status, [MarketplaceListing::STATUS_SOLD, MarketplaceListing::STATUS_REMOVED])) {
            abort(403);
        }

        $categories = \App\Models\MarketplaceCategory::where('type', 'marketplace')
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        return view('marketplace.listings.edit', compact('listing', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $listing = MarketplaceListing::where('user_id', Auth::id())->findOrFail($id);
        if (in_array($listing->status, [MarketplaceListing::STATUS_SOLD, MarketplaceListing::STATUS_REMOVED])) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:20|max:5000',
            'category_id' => 'nullable|exists:marketplace_categories,id',
            'condition' => 'required|in:new,like_new,good,fair,used',
            'price' => 'required|numeric|min:0|max:9999999.99',
            'negotiable' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'available_for_shipping' => 'nullable|boolean',
            'shipping_cost' => 'nullable|numeric|min:0',
            'tags_text' => 'nullable|string|max:1000',
        ]);

        if (array_key_exists('tags_text', $validated)) {
            $validated['tags'] = array_values(array_filter(array_map('trim', explode(',', $validated['tags_text']))));
        }

        if ($request->input('publish')) {
            $validated['status'] = MarketplaceListing::STATUS_PENDING_REVIEW;
            $validated['is_active'] = false;
        }

        $this->listingService->updateListing($listing, $validated);

        return redirect()->route('marketplace.listings.show', $listing->id)
            ->with('success', 'Listing updated successfully!');
    }

    public function destroy($id)
    {
        $listing = MarketplaceListing::where('user_id', Auth::id())->findOrFail($id);

        if ($listing->status === MarketplaceListing::STATUS_SOLD) {
            abort(403, 'Cannot delete a sold listing');
        }

        $listing->delete();

        return redirect()->route('marketplace.listings.mine')
            ->with('success', 'Listing deleted');
    }

    public function toggleFavourite(MarketplaceListing $listing)
    {
        $result = $this->favouriteService->toggle($listing, Auth::user());

        if (request()->ajax()) {
            return response()->json($result);
        }

        return back()->with('success', $result['favourited'] ? 'Added to favourites' : 'Removed from favourites');
    }

    public function myFavourites()
    {
        $favourites = $this->favouriteService->getUserFavourites(Auth::user());
        return view('marketplace.listings.favourites', compact('favourites'));
    }

    public function myListings()
    {
        $listings = $this->listingService->getSellerListings(Auth::user());
        return view('marketplace.listings.mine', compact('listings'));
    }
}