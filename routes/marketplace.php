<?php

use App\Http\Controllers\Marketplace\ListingController;
use App\Http\Controllers\Marketplace\MarketplaceOnboardingController;
use App\Http\Controllers\Marketplace\OrderController;
use App\Http\Controllers\Marketplace\ReviewController;
use App\Http\Controllers\Marketplace\MarketplaceChatController;
use App\Http\Controllers\Marketplace\SellerDashboardController;
use App\Http\Controllers\Marketplace\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Marketplace Routes (marketplace.swiftkudi.app)
|--------------------------------------------------------------------------
|
| All marketplace routes are grouped under the marketplace subdomain.
| Shares session/auth with the main application.
| Feature-gated behind the 'marketplace' feature flag.
|
*/

Route::middleware(['web'])->group(function () {

    // -------------------------------------------------------------------
    // Listing Routes (Public + Protected)
    // -------------------------------------------------------------------
    Route::get('/', [ListingController::class, 'index'])->name('marketplace.listings.index');
    Route::get('/search', [ListingController::class, 'search'])->name('marketplace.listings.search');
    Route::get('/category/{slug}', [ListingController::class, 'category'])->name('marketplace.listings.category');
    Route::get('/listing/{id}', [ListingController::class, 'show'])->name('marketplace.listings.show');

    Route::middleware(['auth'])->prefix('onboarding')->name('marketplace.onboarding.')->group(function () {
        Route::get('/buyer', [MarketplaceOnboardingController::class, 'buyer'])->name('buyer');
        Route::post('/buyer', [MarketplaceOnboardingController::class, 'storeBuyer'])->name('buyer.store');
        Route::get('/seller', [MarketplaceOnboardingController::class, 'seller'])->name('seller');
        Route::post('/seller', [MarketplaceOnboardingController::class, 'storeSeller'])->name('seller.store');
    });

    // Protected listing routes
    Route::middleware(['auth', 'check.email.required'])->group(function () {
        Route::get('/create', [ListingController::class, 'create'])->name('marketplace.listings.create');
        Route::post('/store', [ListingController::class, 'store'])->name('marketplace.listings.store');
        Route::get('/{id}/edit', [ListingController::class, 'edit'])->name('marketplace.listings.edit');
        Route::put('/{id}/update', [ListingController::class, 'update'])->name('marketplace.listings.update');
        Route::delete('/{id}/delete', [ListingController::class, 'destroy'])->name('marketplace.listings.destroy');

        // Favourites
        Route::post('/{id}/favourite', [ListingController::class, 'toggleFavourite'])->name('marketplace.listings.favourite');
        Route::get('/favourites', [ListingController::class, 'myFavourites'])->name('marketplace.listings.favourites');
        Route::get('/my-listings', [ListingController::class, 'myListings'])->name('marketplace.listings.mine');
    });

    // -------------------------------------------------------------------
    // Order Routes (Protected)
    // -------------------------------------------------------------------
    Route::middleware(['auth', 'check.email.required'])->prefix('orders')->name('marketplace.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::post('/{listing}/create', [OrderController::class, 'store'])->name('store');
        Route::post('/{id}/confirm-receipt', [OrderController::class, 'confirmReceipt'])->name('confirm-receipt');
        Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');

        // Seller order management
        Route::get('/sales', [OrderController::class, 'mySales'])->name('sales.index');
        Route::post('/{id}/ship', [OrderController::class, 'markAsShipped'])->name('ship');
        Route::post('/{id}/deliver', [OrderController::class, 'markAsDelivered'])->name('deliver');
    });

    // -------------------------------------------------------------------
    // Review Routes (Protected)
    // -------------------------------------------------------------------
    Route::middleware(['auth', 'check.email.required'])->prefix('reviews')->name('marketplace.reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::get('/create/{order}', [ReviewController::class, 'create'])->name('create');
        Route::post('/store/{order}', [ReviewController::class, 'store'])->name('store');
    });

    // -------------------------------------------------------------------
    // Chat/Messaging Routes (Protected)
    // -------------------------------------------------------------------
    Route::middleware(['auth', 'check.email.required'])->prefix('chat')->name('marketplace.chat.')->group(function () {
        Route::get('/', [MarketplaceChatController::class, 'index'])->name('index');
        Route::get('/{id}', [MarketplaceChatController::class, 'show'])->name('show');
        Route::post('/message', [MarketplaceChatController::class, 'store'])->name('message');
        Route::post('/start', [MarketplaceChatController::class, 'startConversation'])->name('start');
        Route::get('/unread', [MarketplaceChatController::class, 'getUnreadCount'])->name('unread');
        Route::post('/{id}/read', [MarketplaceChatController::class, 'markAsRead'])->name('read');
        Route::post('/{id}/close', [MarketplaceChatController::class, 'closeConversation'])->name('close');
    });

    // -------------------------------------------------------------------
    // Seller Dashboard Routes (Protected)
    // -------------------------------------------------------------------
    Route::middleware(['auth', 'check.email.required'])->prefix('seller')->name('marketplace.seller.')->group(function () {
        Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/listings', [SellerDashboardController::class, 'listings'])->name('listings');
        Route::get('/orders', [SellerDashboardController::class, 'orders'])->name('orders');
        Route::get('/reviews', [SellerDashboardController::class, 'reviews'])->name('reviews');
        Route::get('/profile', [SellerDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [SellerDashboardController::class, 'updateProfile'])->name('profile.update');
    });

    // -------------------------------------------------------------------
    // Subscription Routes (Protected)
    // -------------------------------------------------------------------
    Route::middleware(['auth', 'check.email.required'])->prefix('subscription')->name('marketplace.subscription.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/webhook', [SubscriptionController::class, 'processWebhook'])->name('webhook');
    });
});