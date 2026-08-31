<div>
    <label class="marketplace-label" for="{{ $mobile ? 'mobile-' : '' }}talent-skill">Skill</label>
    <select id="{{ $mobile ? 'mobile-' : '' }}talent-skill" name="skill" class="marketplace-input mt-2"><option value="">Any skill</option>@foreach($allSkills as $skill)<option value="{{ $skill }}" @selected(request('skill') == $skill)>{{ $skill }}</option>@endforeach</select>
</div>
<div>
    <label class="marketplace-label" for="{{ $mobile ? 'mobile-' : '' }}talent-rating">Minimum rating</label>
    <select id="{{ $mobile ? 'mobile-' : '' }}talent-rating" name="min_rating" class="marketplace-input mt-2"><option value="">Any rating</option><option value="4" @selected((string)request('min_rating') === '4')>4.0+</option><option value="4.5" @selected((string)request('min_rating') === '4.5')>4.5+</option></select>
</div>
<div>
    <p class="marketplace-label">Hourly rate</p>
    <div class="mt-2 grid grid-cols-2 gap-2"><input class="marketplace-input" type="number" min="0" name="min_rate" value="{{ request('min_rate') }}" placeholder="Min ₦"><input class="marketplace-input" type="number" min="0" name="max_rate" value="{{ request('max_rate') }}" placeholder="Max ₦"></div>
</div>
<div class="marketplace-option-card"><div class="flex items-start gap-3"><i class="fas fa-circle-check mt-1 text-indigo-400"></i><div><p class="text-sm font-semibold text-gray-200">Availability</p><p class="mt-1 text-xs leading-5 text-gray-500">This directory already shows only profiles marked available by the freelancer.</p></div></div></div>
<div class="flex gap-2"><button type="submit" class="marketplace-btn-primary flex-1">Apply filters</button><a href="{{ route('freelancers.index', array_filter(['search' => request('search'), 'sort' => request('sort')])) }}" class="marketplace-btn-secondary">Reset</a></div>
