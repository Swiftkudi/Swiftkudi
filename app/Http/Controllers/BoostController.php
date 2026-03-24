<?php

namespace App\Http\Controllers;

use App\Models\BoostPackage;
use App\Models\UserBoost;
use App\Models\Task;
use App\Models\ProfessionalService;
use App\Models\DigitalProduct;
use App\Models\GrowthListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoostController extends Controller
{
    /**
     * Display boost packages and user's active boosts.
     */
    public function index()
    {
        $user = Auth::user();
        
        $packages = BoostPackage::where('is_active', true)->orderBy('price')->get();
        $activeBoosts = $user->boosts()
            ->where('expires_at', '>', now())
            ->with('package')
            ->get();

        // Get user's items that can be boosted
        $userItems = $this->getUserBoostableItems($user);

        return view('boost.index', compact('packages', 'activeBoosts', 'userItems'));
    }

    /**
     * Get user's items that can be boosted
     */
    private function getUserBoostableItems($user)
    {
        $items = [];

        // Tasks
        $tasks = Task::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('is_approved', true)
            ->select('id', 'title', 'created_at')
            ->get();
        
        foreach ($tasks as $task) {
            $items[] = [
                'id' => $task->id,
                'type' => 'task',
                'title' => $task->title,
                'created_at' => $task->created_at,
            ];
        }

        // Professional Services
        $services = ProfessionalService::where('user_id', $user->id)
            ->where('status', 'active')
            ->select('id', 'title', 'created_at')
            ->get();
        
        foreach ($services as $service) {
            $items[] = [
                'id' => $service->id,
                'type' => 'service',
                'title' => $service->title,
                'created_at' => $service->created_at,
            ];
        }

        // Digital Products
        $products = DigitalProduct::where('user_id', $user->id)
            ->where('is_active', true)
            ->select('id', 'title', 'created_at')
            ->get();
        
        foreach ($products as $product) {
            $items[] = [
                'id' => $product->id,
                'type' => 'product',
                'title' => $product->title,
                'created_at' => $product->created_at,
            ];
        }

        // Growth Listings
        $growthListings = GrowthListing::where('user_id', $user->id)
            ->where('status', 'active')
            ->select('id', 'title', 'created_at')
            ->get();
        
        foreach ($growthListings as $listing) {
            $items[] = [
                'id' => $listing->id,
                'type' => 'growth',
                'title' => $listing->title,
                'created_at' => $listing->created_at,
            ];
        }

        return $items;
    }

    /**
     * Get user's boostable items via AJAX
     */
    public function getItems(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type', 'all');
        
        $items = [];
        
        if ($type === 'all' || $type === 'task') {
            $tasks = Task::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('is_approved', true)
                ->select('id', 'title')
                ->get();
            
            foreach ($tasks as $task) {
                $items[] = ['id' => $task->id, 'type' => 'task', 'title' => $task->title];
            }
        }
        
        if ($type === 'all' || $type === 'service') {
            $services = ProfessionalService::where('user_id', $user->id)
                ->where('status', 'active')
                ->select('id', 'title')
                ->get();
            
            foreach ($services as $service) {
                $items[] = ['id' => $service->id, 'type' => 'service', 'title' => $service->title];
            }
        }
        
        if ($type === 'all' || $type === 'product') {
            $products = DigitalProduct::where('user_id', $user->id)
                ->where('is_active', true)
                ->select('id', 'title')
                ->get();
            
            foreach ($products as $product) {
                $items[] = ['id' => $product->id, 'type' => 'product', 'title' => $product->title];
            }
        }
        
        if ($type === 'all' || $type === 'growth') {
            $growthListings = GrowthListing::where('user_id', $user->id)
                ->where('status', 'active')
                ->select('id', 'title')
                ->get();
            
            foreach ($growthListings as $listing) {
                $items[] = ['id' => $listing->id, 'type' => 'growth', 'title' => $listing->title];
            }
        }

        return response()->json(['items' => $items]);
    }

    /**
     * Purchase and activate a boost package.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:boost_packages,id',
            'target_type' => 'required|in:task,service,product,growth',
            'target_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $package = BoostPackage::findOrFail($request->package_id);

        // Check if user has sufficient balance
        $totalBalance = $user->wallet->withdrawable_balance + $user->wallet->promo_credit_balance;
        if ($totalBalance < $package->price) {
            return back()->with('error', 'Insufficient balance. Please deposit funds to continue.');
        }

        // Deduct from wallet
        if ($user->wallet->withdrawable_balance >= $package->price) {
            $user->wallet->withdrawable_balance -= $package->price;
        } else {
            $remaining = $package->price - $user->wallet->withdrawable_balance;
            $user->wallet->withdrawable_balance = 0;
            $user->wallet->promo_credit_balance -= $remaining;
        }
        $user->wallet->save();

        // Create boost record
        $boost = new UserBoost([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'target_type' => $request->target_type,
            'target_id' => $request->target_id,
            'started_at' => now(),
            'expires_at' => now()->addDays($package->duration_days),
            'status' => 'active',
        ]);
        $boost->save();

        // Set the target item as featured
        $this->setFeatured($request->target_type, $request->target_id, true);

        return back()->with('success', 'Boost activated successfully! Your listing is now featured.');
    }

    /**
     * Set featured status on an item
     */
    private function setFeatured($type, $id, $featured)
    {
        switch ($type) {
            case 'task':
                Task::where('id', $id)->update(['is_featured' => $featured]);
                break;
            case 'service':
                ProfessionalService::where('id', $id)->update(['is_featured' => $featured]);
                break;
            case 'product':
                DigitalProduct::where('id', $id)->update(['is_featured' => $featured]);
                break;
            case 'growth':
                GrowthListing::where('id', $id)->update(['is_featured' => $featured]);
                break;
        }
    }

    /**
     * Extend boost duration.
     */
    public function extend(Request $request, UserBoost $boost)
    {
        $request->validate([
            'package_id' => 'required|exists:boost_packages,id',
        ]);

        $user = Auth::user();
        $package = BoostPackage::findOrFail($request->package_id);

        if ($boost->user_id !== $user->id) {
            abort(403);
        }

        // Check balance and deduct
        $totalBalance = $user->wallet->withdrawable_balance + $user->wallet->promo_credit_balance;
        if ($totalBalance < $package->price) {
            return back()->with('error', 'Insufficient balance.');
        }

        // Extend expiration
        $boost->expires_at = $boost->expires_at->addDays($package->duration_days);
        $boost->save();

        // Deduct from wallet
        if ($user->wallet->withdrawable_balance >= $package->price) {
            $user->wallet->withdrawable_balance -= $package->price;
        } else {
            $remaining = $package->price - $user->wallet->withdrawable_balance;
            $user->wallet->withdrawable_balance = 0;
            $user->wallet->promo_credit_balance -= $remaining;
        }
        $user->wallet->save();

        return back()->with('success', 'Boost extended successfully!');
    }

    /**
     * Cancel an active boost.
     */
    public function cancel(UserBoost $boost)
    {
        $user = Auth::user();

        if ($boost->user_id !== $user->id) {
            abort(403);
        }

        if ($boost->expires_at <= now()) {
            return back()->with('error', 'This boost has already expired.');
        }

        // Calculate refund (50% of remaining days)
        $remainingDays = now()->diffInDays($boost->expires_at);
        $totalDays = $boost->started_at->diffInDays($boost->expires_at);
        
        if ($remainingDays > 0 && $totalDays > 0) {
            $refundPercentage = ($remainingDays / $totalDays) * 0.5;
            $package = $boost->package;
            $refundAmount = $package->price * $refundPercentage;
            
            // Refund to wallet
            $user->wallet->withdrawable_balance += $refundAmount;
            $user->wallet->save();
        }

        // Remove featured status from the item
        $this->setFeatured($boost->target_type, $boost->target_id, false);

        $boost->status = 'cancelled';
        $boost->save();

        return back()->with('success', 'Boost cancelled. Partial refund has been credited to your wallet.');
    }
}
