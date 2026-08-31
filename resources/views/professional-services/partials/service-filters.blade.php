<div>
    <label for="{{ $mobile ? 'mobile-' : '' }}filter-category" class="marketplace-label">Category</label>
    <select id="{{ $mobile ? 'mobile-' : '' }}filter-category" name="category" class="marketplace-input mt-2">
        <option value="">All categories</option>
        @foreach($categories as $category)<option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>@endforeach
    </select>
</div>
<div>
    <p class="marketplace-label">Price</p>
    <div class="mt-2 grid grid-cols-2 gap-2"><input class="marketplace-input" type="number" min="0" step="100" name="min_price" value="{{ request('min_price') }}" placeholder="Min ₦"><input class="marketplace-input" type="number" min="0" step="100" name="max_price" value="{{ request('max_price') }}" placeholder="Max ₦"></div>
</div>
<div>
    <label for="{{ $mobile ? 'mobile-' : '' }}delivery-days" class="marketplace-label">Delivery within</label>
    <select id="{{ $mobile ? 'mobile-' : '' }}delivery-days" name="delivery_days" class="marketplace-input mt-2"><option value="">Any time</option>@foreach([1 => '1 day',3 => '3 days',7 => '7 days',14 => '14 days',30 => '30 days'] as $days => $label)<option value="{{ $days }}" @selected((string)request('delivery_days') === (string)$days)>{{ $label }}</option>@endforeach</select>
</div>
<div>
    <label for="{{ $mobile ? 'mobile-' : '' }}service-sort" class="marketplace-label">Sort by</label>
    <select id="{{ $mobile ? 'mobile-' : '' }}service-sort" name="sort" class="marketplace-input mt-2"><option value="recommended" @selected(request('sort','recommended') === 'recommended')>Recommended</option><option value="newest" @selected(request('sort') === 'newest')>Newest</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select>
</div>
<div class="flex gap-2"><button type="submit" class="marketplace-btn-primary flex-1">Apply filters</button><a href="{{ route('professional-services.index', array_filter(['search' => request('search')])) }}" class="marketplace-btn-secondary">Reset</a></div>
