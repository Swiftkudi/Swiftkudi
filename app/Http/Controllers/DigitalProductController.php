<?php

namespace App\Http\Controllers;

use App\Models\DigitalProduct;
use App\Models\DigitalProductOrder;
use App\Models\DigitalProductReview;
use App\Models\MarketplaceConversation;
use App\Models\MarketplaceMessage;
use App\Models\MarketplaceCategory;
use App\Services\DigitalProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DigitalProductController extends Controller
{
    protected $service;

    public function __construct(DigitalProductService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $query = DigitalProduct::active()->with(['user', 'category']);

        // Add buyer category filter
        $user = auth()->user();
        if ($user && $user->account_type === 'buyer' && $user->buyer_onboarding_completed) {
            $buyerCategories = $user->getBuyerCategories();
            if (!empty($buyerCategories)) {
                $query->whereIn('category_id', $buyerCategories);
            }
        }

        if ($request->category) {
            $query->byCategory($request->category);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        if ($request->sort == 'price_low') {
            $query->orderBy('price', 'asc');
        } elseif ($request->sort == 'price_high') {
            $query->orderBy('price', 'desc');
        } elseif ($request->sort == 'popular') {
            $query->orderBy('total_sales', 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(12);
        
        // Get categories - buyers only see their selected categories
        $user = auth()->user();
        if ($user && $user->account_type === 'buyer' && $user->buyer_onboarding_completed) {
            $buyerCategories = $user->getBuyerCategories();
            if (!empty($buyerCategories)) {
                // Only show buyer's selected categories
                $categories = MarketplaceCategory::where('type', 'digital_product')
                    ->whereIn('id', $buyerCategories)
                    ->get();
            } else {
                $categories = MarketplaceCategory::where('type', 'digital_product')->get();
            }
        } else {
            $categories = MarketplaceCategory::where('type', 'digital_product')->get();
        }

        return view('digital-products.index', compact('products', 'categories'));
    }

    public function featured()
    {
        $products = DigitalProduct::active()
            ->featured()
            ->with(['user', 'category'])
            ->latest()
            ->take(8)
            ->get();

        return view('digital-products.featured', compact('products'));
    }

    public function show(DigitalProduct $product)
    {
        $product->load(['user', 'category', 'reviews.user']);
        
        $relatedProducts = DigitalProduct::active()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->take(4)
            ->get();

        return view('digital-products.show', compact('product', 'relatedProducts'));
    }

    public function myProducts()
    {
        $products = DigitalProduct::where('user_id', Auth::id())
            ->with('category')
            ->latest()
            ->paginate(10);

        return view('digital-products.my-products', compact('products'));
    }

    public function myPurchases()
    {
        $orders = DigitalProductOrder::where('buyer_id', Auth::id())
            ->with('product')
            ->latest()
            ->paginate(10);

        return view('digital-products.my-purchases', compact('orders'));
    }

    public function create()
    {
        $digitalCategories = MarketplaceCategory::where('type', 'digital_product')->get();
        $physicalCategories = MarketplaceCategory::where('type', 'physical_product')->get();
        return view('digital-products.create', compact('digitalCategories', 'physicalCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:marketplace_categories,id',
            'thumbnail' => 'nullable|image|max:2048',
            'file' => 'required|file|max:51200', // 50MB max
            'tags' => 'nullable|string',
            'license_type' => 'required|in:1,2,3',
            'version' => 'nullable|string',
            'changelog' => 'nullable|string',
            'requirements' => 'nullable|string',
            'is_free' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['is_free'] = $request->boolean('is_free');
        
        if ($data['is_free']) {
            $data['price'] = 0;
        }

        $product = $this->service->createProduct($data, Auth::id());

        app(\App\Services\NotificationDispatchService::class)->sendToUser(
            Auth::user(),
            'Product Created',
            'Your digital product "' . $product->title . '" has been created successfully.',
            \App\Models\Notification::TYPE_SYSTEM,
            ['product_id' => $product->id, 'action_url' => route('digital-products.show', $product)],
            'notify_product_orders',
            true
        );

        if (Auth::user()->account_type === 'digital_seller' && !Auth::user()->digital_product_uploaded) {
            // Use centralized service for unlock logic
            app(\App\Services\TaskGateProgressService::class)->unlockMarketplaceSeller(
                Auth::user(),
                'digital_seller'
            );
        }

        return redirect()->route('digital-products.show', $product)
            ->with('success', 'Product created successfully!');
    }

    public function edit(DigitalProduct $product)
    {
        $this->authorize('update', $product);
        
        $categories = MarketplaceCategory::where('type', 'digital_product')->get();
        return view('digital-products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, DigitalProduct $product)
    {
        $this->authorize('update', $product);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:marketplace_categories,id',
            'thumbnail' => 'nullable|image|max:2048',
            'file' => 'nullable|file|max:51200',
            'tags' => 'nullable|string',
            'license_type' => 'required|in:1,2,3',
            'version' => 'nullable|string',
            'changelog' => 'nullable|string',
            'requirements' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active', true);

        $product = $this->service->updateProduct($product, $data);

        return redirect()->route('digital-products.show', $product)
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(DigitalProduct $product)
    {
        $this->authorize('delete', $product);

        $this->service->deleteProduct($product);

        return redirect()->route('digital-products.my-products')
            ->with('success', 'Product deleted successfully!');
    }

    public function purchase(DigitalProduct $product)
    {
        if ($product->user_id === Auth::id()) {
            return back()->with('error', 'You cannot purchase your own product.');
        }

        if (!$product->is_active) {
            return back()->with('error', 'This product is not available.');
        }

        if (!$product->is_free) {
            $user = Auth::user();
            $wallet = $user->wallet;
            $available = (float) ($wallet->withdrawable_balance ?? 0) + (float) ($wallet->promo_credit_balance ?? 0);
            $required = (float) $product->current_price;

            if ($available < $required) {
                $requiredTopup = max(0, $required - $available);

                session([
                    'pending_product_checkout' => [
                        'product_id' => $product->id,
                    ],
                    'deposit_success_redirect' => route('digital-products.purchase.resume'),
                    'insufficient_balance_required' => $requiredTopup,
                ]);

                return redirect()
                    ->route('wallet.deposit', ['required' => $requiredTopup])
                    ->with('error', 'Insufficient wallet balance. Deposit and you will be returned to complete this purchase.');
            }
        }

        try {
            $order = $this->service->purchaseProduct($product, Auth::id());

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $product->user,
                'New Product Purchase',
                'Your product "' . $product->title . '" was purchased. Order: ' . $order->order_number . '.',
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $order->id, 'product_id' => $product->id, 'action_url' => route('digital-products.my-purchases')],
                'notify_product_orders',
                true
            );

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                Auth::user(),
                'Purchase Successful',
                'You purchased "' . $product->title . '" successfully. Order: ' . $order->order_number . '.',
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $order->id, 'product_id' => $product->id, 'action_url' => route('digital-products.my-purchases')],
                'notify_product_orders'
            );

            try {
                $conversation = MarketplaceConversation::findOrCreate(
                    'digital_product',
                    $product->id,
                    $order->buyer_id,
                    $product->user_id
                );

                MarketplaceMessage::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $order->buyer_id,
                    'message' => 'I just purchased "' . $product->title . '" (Order: ' . $order->order_number . ').',
                    'is_read' => false,
                ]);

                $conversation->update(['last_message_at' => now()]);
            } catch (\Exception $e) {
                Log::warning('Failed to create digital purchase conversation', ['error' => $e->getMessage()]);
            }

            if ($product->is_free) {
                return redirect()->route('digital-products.download', $order)
                    ->with('success', 'Download started!');
            }

            return redirect()->route('digital-products.my-purchases')
                ->with('success', 'Purchase completed! You can now download your product.');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            
            // Provide specific error messages
            if (strpos($message, 'Insufficient balance') !== false) {
                return back()->with('error', 'Insufficient wallet balance. Please fund your wallet to purchase this product.');
            }
            
            // Log the actual error for debugging
            Log::error('Digital product purchase error: ' . $message, [
                'product_id' => $product->id,
                'user_id' => Auth::id(),
            ]);
            
            return back()->with('error', $message ?: 'An error occurred while processing your purchase. Please try again.');
        }
    }

    public function resumePurchase(Request $request)
    {
        $pending = session('pending_product_checkout');

        if (!$pending || empty($pending['product_id'])) {
            return redirect()->route('digital-products.index')->with('error', 'No pending product purchase found to resume.');
        }

        $product = DigitalProduct::find($pending['product_id']);

        if (!$product || !$product->is_active) {
            session()->forget(['pending_product_checkout', 'deposit_success_redirect', 'insufficient_balance_required']);
            return redirect()->route('digital-products.index')->with('error', 'The selected product is no longer available.');
        }

        if ($product->user_id === Auth::id()) {
            session()->forget(['pending_product_checkout', 'deposit_success_redirect', 'insufficient_balance_required']);
            return redirect()->route('digital-products.show', $product)->with('error', 'You cannot purchase your own product.');
        }

        try {
            $order = $this->service->purchaseProduct($product, Auth::id());

            app(\App\Services\NotificationDispatchService::class)->sendToUser(
                $product->user,
                'Product Checkout Resumed',
                'A buyer resumed checkout and purchased "' . $product->title . '". Order: ' . $order->order_number . '.',
                \App\Models\Notification::TYPE_SYSTEM,
                ['order_id' => $order->id, 'product_id' => $product->id, 'action_url' => route('digital-products.my-purchases')],
                'notify_product_orders',
                true
            );

            try {
                $conversation = MarketplaceConversation::findOrCreate(
                    'digital_product',
                    $product->id,
                    $order->buyer_id,
                    $product->user_id
                );

                MarketplaceMessage::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $order->buyer_id,
                    'message' => 'I just completed checkout for "' . $product->title . '" (Order: ' . $order->order_number . ').',
                    'is_read' => false,
                ]);

                $conversation->update(['last_message_at' => now()]);
            } catch (\Exception $e) {
                Log::warning('Failed to create resumed digital purchase conversation', ['error' => $e->getMessage()]);
            }

            session()->forget(['pending_product_checkout', 'deposit_success_redirect', 'insufficient_balance_required']);

            if ($product->is_free) {
                return redirect()->route('digital-products.download', $order)
                    ->with('success', 'Download started!');
            }

            return redirect()->route('digital-products.my-purchases')
                ->with('success', 'Purchase completed successfully after deposit.');
        } catch (\Exception $e) {
            if (str_contains(strtolower($e->getMessage()), 'insufficient')) {
                $user = Auth::user();
                $wallet = $user->wallet;
                $available = (float) ($wallet->withdrawable_balance ?? 0) + (float) ($wallet->promo_credit_balance ?? 0);
                $requiredTopup = max(0, (float) $product->current_price - $available);

                session([
                    'deposit_success_redirect' => route('digital-products.purchase.resume'),
                    'insufficient_balance_required' => $requiredTopup,
                ]);

                return redirect()->route('wallet.deposit', ['required' => $requiredTopup])
                    ->with('error', 'Your balance is still insufficient. Please complete your deposit to continue.');
            }

            Log::error('Failed to resume digital purchase', [
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('digital-products.show', $product)
                ->with('error', 'Failed to complete resumed purchase. Please try again.');
        }
    }

    public function download(DigitalProductOrder $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        $downloadUrl = $this->service->processDownload($order);

        if (!$downloadUrl) {
            return back()->with('error', 'Download limit reached or expired.');
        }

        return redirect($downloadUrl);
    }

    public function review(Request $request, DigitalProduct $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $this->service->addReview($product, Auth::id(), $request->all());

        return back()->with('success', 'Review submitted successfully!');
    }

    public function confirmReceipt(Request $request, DigitalProductOrder $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        $result = $this->service->confirmReceiptAndReleaseWithReview($order, Auth::id(), $validated);

        if (!$result['success']) {
            return back()->with('error', $result['message'] ?? 'Failed to confirm receipt.');
        }

        return back()->with('success', $result['message']);
    }

    public function feature(DigitalProduct $product)
    {
        $this->authorize('feature', $product);
        
        $product = $this->service->featureProduct($product);
        
        return back()->with('success', 'Product is now featured!');
    }

    public function unfeature(DigitalProduct $product)
    {
        $this->authorize('feature', $product);
        
        $product = $this->service->unfeatureProduct($product);
        
        return back()->with('success', 'Product is no longer featured.');
    }
}
