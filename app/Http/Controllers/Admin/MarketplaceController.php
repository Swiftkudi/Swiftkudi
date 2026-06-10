<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MarketplaceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display marketplace management dashboard
     */
    public function index()
    {
        $categories = MarketplaceCategory::withCount(['children'])
            ->with('parent')
            ->orderBy('type')
            ->orderBy('order')
            ->paginate(20);

        $stats = [
            'total_categories' => MarketplaceCategory::count(),
            'active_categories' => MarketplaceCategory::where('is_active', true)->count(),
            'task_categories' => MarketplaceCategory::where('type', 'task')->count(),
            'professional_categories' => MarketplaceCategory::where('type', 'professional')->count(),
            'growth_categories' => MarketplaceCategory::where('type', 'growth')->count(),
            'digital_product_categories' => MarketplaceCategory::where('type', 'digital_product')->count(),
            'job_categories' => MarketplaceCategory::where('type', 'job')->count(),
        ];

        return view('admin.marketplace.index', compact('categories', 'stats'));
    }

    /**
     * Show form for creating category
     */
    public function create()
    {
        $types = [
            'task' => 'Task',
            'professional' => 'Professional Service',
            'growth' => 'Growth Service',
            'digital_product' => 'Digital Product',
            'job' => 'Job',
        ];

        $parents = MarketplaceCategory::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.marketplace.create', compact('types', 'parents'));
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:task,professional,growth,digital_product,job',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'parent_id' => 'nullable|exists:marketplace_categories,id',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Ensure unique slug
        $slug = $validated['slug'];
        $count = MarketplaceCategory::where('slug', 'like', $slug . '%')->count();
        if ($count > 0) {
            $validated['slug'] = $slug . '-' . ($count + 1);
        }

        MarketplaceCategory::create($validated);

        return redirect()->route('admin.marketplace.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Show form for editing category
     */
    public function edit(MarketplaceCategory $category)
    {
        $types = [
            'task' => 'Task',
            'professional' => 'Professional Service',
            'growth' => 'Growth Service',
            'digital_product' => 'Digital Product',
            'job' => 'Job',
        ];

        $parents = MarketplaceCategory::where('id', '!=', $category->id)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.marketplace.edit', compact('category', 'types', 'parents'));
    }

    /**
     * Update category
     */
    public function update(Request $request, MarketplaceCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:task,professional,growth,digital_product,job',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'parent_id' => 'nullable|exists:marketplace_categories,id',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        // Update slug if name changed
        if ($category->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
            $slug = $validated['slug'];
            $count = MarketplaceCategory::where('id', '!=', $category->id)
                ->where('slug', 'like', $slug . '%')
                ->count();
            if ($count > 0) {
                $validated['slug'] = $slug . '-' . ($count + 1);
            }
        }

        $category->update($validated);

        return redirect()->route('admin.marketplace.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Delete category
     */
    public function destroy(MarketplaceCategory $category)
    {
        // Check if category has children
        if ($category->children()->count() > 0) {
            return back()->with('error', 'Cannot delete category with subcategories. Please delete or move subcategories first.');
        }

        $category->delete();

        return redirect()->route('admin.marketplace.index')
            ->with('success', 'Category deleted successfully!');
    }

    /**
     * Toggle category status
     */
    public function toggle(Request $request, MarketplaceCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Category {$status} successfully!");
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'categories' => 'required|array',
            'categories.*' => 'exists:marketplace_categories,id',
        ]);

        $categories = MarketplaceCategory::whereIn('id', $request->categories);

        switch ($request->action) {
            case 'activate':
                $categories->update(['is_active' => true]);
                $message = 'Categories activated successfully!';
                break;
            case 'deactivate':
                $categories->update(['is_active' => false]);
                $message = 'Categories deactivated successfully!';
                break;
            case 'delete':
                // Check for categories with children
                $withChildren = MarketplaceCategory::whereIn('id', $request->categories)
                    ->whereHas('children')
                    ->count();
                
                if ($withChildren > 0) {
                    return back()->with('error', 'Some categories have subcategories. Please remove them first.');
                }
                
                $categories->delete();
                $message = 'Categories deleted successfully!';
                break;
        }

        return redirect()->route('admin.marketplace.index')
            ->with('success', $message);
    }

    /**
     * Feature toggles management
     */
    public function features()
    {
        $features = [
            'professional_services' => [
                'name' => 'Professional Services',
                'description' => 'Enable professional services marketplace',
                'enabled' => system_setting('professional_services_enabled', true),
            ],
            'growth_marketplace' => [
                'name' => 'Growth Marketplace',
                'description' => 'Enable growth services and backlinks marketplace',
                'enabled' => system_setting('growth_marketplace_enabled', true),
            ],
            'digital_products' => [
                'name' => 'Digital Products',
                'description' => 'Enable digital products marketplace',
                'enabled' => system_setting('digital_products_enabled', true),
            ],
            'job_board' => [
                'name' => 'Job Board',
                'description' => 'Enable job board feature',
                'enabled' => system_setting('job_board_enabled', false),
            ],
            'boost_system' => [
                'name' => 'Boost System',
                'description' => 'Enable task/service boosting',
                'enabled' => system_setting('boost_system_enabled', false),
            ],
            'subscriptions' => [
                'name' => 'Subscriptions',
                'description' => 'Enable premium subscriptions',
                'enabled' => system_setting('subscriptions_enabled', false),
            ],
            'user_verification' => [
                'name' => 'User Verification',
                'description' => 'Enable multi-level user verification',
                'enabled' => system_setting('user_verification_enabled', false),
            ],
            'referral_system' => [
                'name' => 'Referral System',
                'description' => 'Enable referral bonus system',
                'enabled' => system_setting('referral_system_enabled', true),
            ],
            'escrow_system' => [
                'name' => 'Escrow System',
                'description' => 'Enable escrow payments',
                'enabled' => system_setting('escrow_system_enabled', true),
            ],
            'real_time_chat' => [
                'name' => 'Real-time Chat',
                'description' => 'Enable messaging between users',
                'enabled' => system_setting('real_time_chat_enabled', true),
            ],
        ];

        return view('admin.marketplace.features', compact('features'));
    }

    /**
     * Toggle feature on/off
     */
    public function toggleFeature(Request $request)
    {
        $request->validate([
            'feature' => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        $featureKey = $request->feature . '_enabled';
        system_setting($featureKey, $request->enabled);

        $status = $request->enabled ? 'enabled' : 'disabled';

        return back()->with('success', "Feature {$status} successfully!");
    }
}
