<div>
    <label class="marketplace-label" for="{{ $mobile ? 'mobile-' : '' }}job-category">Category</label>
    <select id="{{ $mobile ? 'mobile-' : '' }}job-category" name="category" class="marketplace-input mt-2">
        <option value="">All categories</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected((string)request('category') === (string)$category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="marketplace-label" for="{{ $mobile ? 'mobile-' : '' }}job-type">Work type</label>
    <select id="{{ $mobile ? 'mobile-' : '' }}job-type" name="type" class="marketplace-input mt-2">
        <option value="">Any type</option>
        @foreach(['full-time' => 'Full time','part-time' => 'Part time','contract' => 'Contract','internship' => 'Internship'] as $value => $label)
            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="marketplace-label" for="{{ $mobile ? 'mobile-' : '' }}job-level">Experience level</label>
    <select id="{{ $mobile ? 'mobile-' : '' }}job-level" name="level" class="marketplace-input mt-2">
        <option value="">Any experience</option>
        @foreach(['entry' => 'Entry level','intermediate' => 'Intermediate','expert' => 'Expert'] as $value => $label)
            <option value="{{ $value }}" @selected(request('level') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <p class="marketplace-label">Budget</p>
    <div class="mt-2 grid grid-cols-2 gap-2"><input class="marketplace-input" type="number" min="0" name="budget_min" value="{{ request('budget_min') }}" placeholder="Min ₦"><input class="marketplace-input" type="number" min="0" name="budget_max" value="{{ request('budget_max') }}" placeholder="Max ₦"></div>
</div>
<div>
    <label class="marketplace-label" for="{{ $mobile ? 'mobile-' : '' }}job-location">Location</label>
    <input id="{{ $mobile ? 'mobile-' : '' }}job-location" type="text" name="location" value="{{ request('location') }}" maxlength="120" class="marketplace-input mt-2" placeholder="e.g. Lagos or Remote">
</div>
@auth
<label class="marketplace-option-card flex items-center gap-3">
    <input class="marketplace-checkbox" type="checkbox" name="saved" value="1" @checked(request()->boolean('saved'))>
    <span class="text-sm font-medium text-gray-200">Saved jobs only</span>
</label>
@endauth
<div class="flex gap-2 pt-1"><button type="submit" class="marketplace-btn-primary flex-1">Apply filters</button><a href="{{ route('jobs.index', array_filter(['search' => request('search'), 'sort' => request('sort')])) }}" class="marketplace-btn-secondary">Reset</a></div>
