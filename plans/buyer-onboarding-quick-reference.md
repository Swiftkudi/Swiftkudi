# Buyer Onboarding - Quick Reference Guide

## Key Requirements Summary

### ✅ What Buyers Get
- **No activation fee** - Browse immediately after category selection
- **Personalized marketplace** - Only see selected categories
- **Flexible preferences** - Update categories anytime from settings
- **Immediate access** - No mandatory creation requirements

### ❌ What Buyers Don't Get
- Cannot create listings/services/products
- Cannot access seller-only features
- Cannot see categories they didn't select
- No activation fee payment required

## Database Changes

### New Fields in `users` Table
```php
buyer_categories_selected      JSON      // Array of category IDs
buyer_onboarding_completed     BOOLEAN   // Category selection done
```

### Example Data
```json
{
  "buyer_categories_selected": [1, 3, 5, 7, 12, 15],
  "buyer_onboarding_completed": true
}
```

## Code Snippets

### User Model Methods
```php
// Check if buyer has access to a category
public function hasBuyerCategoryAccess(int $categoryId): bool
{
    if ($this->account_type !== 'buyer') {
        return true; // Non-buyers see all
    }
    
    $categories = $this->buyer_categories_selected ?? [];
    return in_array($categoryId, $categories);
}

// Get buyer's selected categories
public function getBuyerCategories(): array
{
    return $this->buyer_categories_selected ?? [];
}

// Update buyer categories
public function setBuyerCategories(array $categoryIds): void
{
    $this->buyer_categories_selected = $categoryIds;
    $this->buyer_onboarding_completed = true;
    $this->save();
}
```

### Controller Filtering Pattern
```php
// Apply to all marketplace controllers
public function index(Request $request)
{
    $query = Model::active();
    
    // Buyer category filtering
    if (Auth::check() && Auth::user()->account_type === 'buyer') {
        $buyerCategories = Auth::user()->getBuyerCategories();
        if (!empty($buyerCategories)) {
            $query->whereIn('category_id', $buyerCategories);
        }
    }
    
    // ... rest of logic
}
```

### Middleware Check
```php
// In EnsureBuyerAccess middleware
if ($user->account_type === 'buyer' && !$user->buyer_onboarding_completed) {
    return redirect()->route('onboarding.buyer.categories')
        ->with('warning', 'Please select your preferred categories to continue.');
}
```

### Validation Rules
```php
// Category selection validation
$request->validate([
    'categories' => 'required|array|min:1',
    'categories.*' => 'exists:marketplace_categories,id',
]);
```

## Routes to Add

```php
// Buyer onboarding
Route::get('/onboarding/buyer/categories', [OnboardingController::class, 'buyerCategorySelection'])
    ->name('onboarding.buyer.categories');
Route::post('/onboarding/buyer/categories', [OnboardingController::class, 'storeBuyerCategories'])
    ->name('onboarding.buyer.categories.store');

// Buyer settings
Route::get('/settings/buyer-categories', [SettingsController::class, 'buyerCategoriesForm'])
    ->name('settings.buyer-categories');
Route::post('/settings/buyer-categories', [SettingsController::class, 'updateBuyerCategories'])
    ->name('settings.buyer-categories.update');
```

## Middleware Registration

```php
// In app/Http/Kernel.php
protected $routeMiddleware = [
    // ... existing middleware
    'buyer.access' => \App\Http\Middleware\EnsureBuyerAccess::class,
];
```

## View Components

### Category Selection Checkbox
```blade
<div class="category-item">
    <label class="flex items-center space-x-3 p-4 border rounded-lg hover:bg-gray-50 cursor-pointer">
        <input 
            type="checkbox" 
            name="categories[]" 
            value="{{ $category->id }}"
            class="w-5 h-5 text-indigo-600"
            {{ in_array($category->id, old('categories', $selectedCategories ?? [])) ? 'checked' : '' }}
        >
        <div class="flex-1">
            <h4 class="font-semibold">{{ $category->name }}</h4>
            <p class="text-sm text-gray-500">{{ $category->description }}</p>
        </div>
    </label>
</div>
```

### Select All Button
```blade
<button 
    type="button" 
    onclick="selectAllInGroup('professional-services')"
    class="text-sm text-indigo-600 hover:text-indigo-800"
>
    Select All
</button>

<script>
function selectAllInGroup(group) {
    const checkboxes = document.querySelectorAll(`[data-group="${group}"] input[type="checkbox"]`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}
</script>
```

## Testing Checklist

### Unit Tests
- [ ] User model methods work correctly
- [ ] Category validation passes/fails appropriately
- [ ] Middleware redirects correctly
- [ ] Controller filtering works

### Integration Tests
- [ ] Complete onboarding flow
- [ ] Category selection saves correctly
- [ ] Marketplace shows filtered results
- [ ] Settings update works
- [ ] Dashboard displays correctly

### Manual Testing
- [ ] Register as buyer
- [ ] Select categories (various combinations)
- [ ] Browse each marketplace section
- [ ] Verify only selected categories visible
- [ ] Update categories from settings
- [ ] Check dashboard content

## Common Issues & Solutions

### Issue: Categories not filtering
**Solution**: Check if `buyer_categories_selected` is properly cast to array in User model

### Issue: Middleware redirect loop
**Solution**: Ensure onboarding routes are excluded from middleware checks

### Issue: Empty marketplace
**Solution**: Verify categories are active and have items

### Issue: "Select All" not working
**Solution**: Check JavaScript group selectors match data attributes

## Performance Tips

1. **Cache category lists**: Don't query categories on every request
2. **Index JSON field**: Add index to `buyer_categories_selected` for faster queries
3. **Eager load relationships**: Use `with()` to prevent N+1 queries
4. **Limit results**: Always paginate marketplace results

## Security Checklist

- [ ] Validate category IDs exist before saving
- [ ] Prevent SQL injection in WHERE IN queries
- [ ] Check user authentication before filtering
- [ ] Sanitize all user inputs
- [ ] Rate limit category updates
- [ ] Log suspicious activity

## Deployment Steps

1. **Backup database** before running migration
2. **Run migration**: `php artisan migrate`
3. **Clear cache**: `php artisan cache:clear`
4. **Test on staging** environment first
5. **Monitor logs** for errors after deployment
6. **Notify users** about new buyer features

## Rollback Plan

If issues occur:
1. Revert migration: `php artisan migrate:rollback`
2. Restore previous code version
3. Clear all caches
4. Verify system stability
5. Investigate root cause

## Support Documentation

### For Users
- How to select categories during onboarding
- How to update category preferences
- What each category includes
- Why they only see certain items

### For Developers
- Architecture overview
- Code structure
- Testing procedures
- Troubleshooting guide

## Monitoring Metrics

Track these after deployment:
- Buyer onboarding completion rate
- Average categories selected per buyer
- Most popular categories
- Time to complete onboarding
- Category update frequency
- Marketplace engagement by category

## Future Enhancements

1. **Smart recommendations** based on browsing history
2. **Category bundles** (e.g., "Digital Marketing Pack")
3. **Temporary category access** for special promotions
4. **Category-based notifications** for new items
5. **AI-powered category suggestions**

---

## Quick Commands

```bash
# Create migration
php artisan make:migration add_buyer_fields_to_users_table

# Create middleware
php artisan make:middleware EnsureBuyerAccess

# Clear cache after changes
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run tests
php artisan test --filter BuyerOnboarding
```

## Contact & Support

For questions or issues during implementation:
- Review the detailed plan: `buyer-onboarding-implementation-plan.md`
- Check architecture: `buyer-onboarding-architecture.md`
- Consult existing onboarding code for patterns
- Test thoroughly before deploying to production

---

**Remember**: This feature should enhance the buyer experience without disrupting existing seller onboarding flows. Test extensively!
