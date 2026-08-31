@php
    $editing = isset($job);
    $requirementsValue = old('requirements', $editing ? implode("\n", (array) ($job->requirements ?? [])) : '');
    $benefitsValue = old('benefits', $editing ? implode("\n", (array) ($job->benefits ?? [])) : '');
@endphp

@if ($errors->any())
    <div class="marketplace-card border-red-500/40 bg-red-500/5 p-4" role="alert">
        <div class="flex items-start gap-3">
            <i class="fas fa-circle-exclamation mt-0.5 text-red-400" aria-hidden="true"></i>
            <div>
                <p class="font-semibold text-red-200">Please review the highlighted information.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-200/90">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-start">
    <div class="space-y-6">
        <section class="marketplace-card p-5 sm:p-6" aria-labelledby="job-scope-heading">
            <div class="mb-5 border-b border-slate-800 pb-4">
                <p class="marketplace-eyebrow">1. Scope</p>
                <h2 id="job-scope-heading" class="mt-1 text-lg font-semibold text-slate-100">Tell talent what you need</h2>
                <p class="mt-1 text-sm leading-6 text-slate-400">Use a clear title and enough context for freelancers to decide whether the opportunity fits them.</p>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="job-title" class="marketplace-label">Job title <span class="text-red-400">*</span></label>
                    <input id="job-title" type="text" name="title" maxlength="160" required
                        value="{{ old('title', $editing ? $job->title : '') }}"
                        class="marketplace-input"
                        placeholder="e.g. Laravel developer for marketplace improvements">
                    <p class="mt-1.5 text-xs text-slate-500">Keep it specific and easy to scan.</p>
                    @error('title')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="job-category" class="marketplace-label">Category <span class="text-red-400">*</span></label>
                    <select id="job-category" name="category_id" required class="marketplace-input">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $editing ? $job->category_id : '') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <div class="flex items-end justify-between gap-4">
                        <label for="job-description" class="marketplace-label">Description <span class="text-red-400">*</span></label>
                        <span class="mb-2 text-xs text-slate-500">Maximum 20,000 characters</span>
                    </div>
                    <textarea id="job-description" name="description" rows="9" maxlength="20000" required
                        class="marketplace-input min-h-[220px] resize-y"
                        placeholder="Describe the outcome you need, responsibilities, important context, and what a successful result looks like.">{{ old('description', $editing ? $job->description : '') }}</textarea>
                    @error('description')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="marketplace-card p-5 sm:p-6" aria-labelledby="job-details-heading">
            <div class="mb-5 border-b border-slate-800 pb-4">
                <p class="marketplace-eyebrow">2. Engagement</p>
                <h2 id="job-details-heading" class="mt-1 text-lg font-semibold text-slate-100">Set the working details</h2>
                <p class="mt-1 text-sm leading-6 text-slate-400">These fields are shown in Find Work and help relevant freelancers compare opportunities.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="job-type" class="marketplace-label">Job type <span class="text-red-400">*</span></label>
                    <select id="job-type" name="job_type" required class="marketplace-input">
                        <option value="">Select type</option>
                        <option value="full-time" @selected(old('job_type', $editing ? $job->job_type : '') === 'full-time')>Full time</option>
                        <option value="part-time" @selected(old('job_type', $editing ? $job->job_type : '') === 'part-time')>Part time</option>
                        <option value="contract" @selected(old('job_type', $editing ? $job->job_type : '') === 'contract')>Contract</option>
                        <option value="internship" @selected(old('job_type', $editing ? $job->job_type : '') === 'internship')>Internship</option>
                    </select>
                    @error('job_type')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="experience-level" class="marketplace-label">Experience level <span class="text-red-400">*</span></label>
                    <select id="experience-level" name="experience_level" required class="marketplace-input">
                        <option value="">Select level</option>
                        <option value="entry" @selected(old('experience_level', $editing ? $job->experience_level : '') === 'entry')>Entry level</option>
                        <option value="intermediate" @selected(old('experience_level', $editing ? $job->experience_level : '') === 'intermediate')>Intermediate</option>
                        <option value="expert" @selected(old('experience_level', $editing ? $job->experience_level : '') === 'expert')>Expert</option>
                    </select>
                    @error('experience_level')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="job-duration" class="marketplace-label">Expected duration</label>
                    <input id="job-duration" type="text" name="duration" maxlength="100"
                        value="{{ old('duration', $editing ? $job->duration : '') }}"
                        class="marketplace-input" placeholder="e.g. 3 months or ongoing">
                    @error('duration')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="job-location" class="marketplace-label">Location</label>
                    <input id="job-location" type="text" name="location" maxlength="255"
                        value="{{ old('location', $editing ? $job->location : '') }}"
                        class="marketplace-input" placeholder="e.g. Remote or Lagos, Nigeria">
                    @error('location')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="positions-available" class="marketplace-label">Positions to fill <span class="text-red-400">*</span></label>
                    <input id="positions-available" type="number" name="positions_available" min="1" max="50" required
                        value="{{ old('positions_available', $editing ? $job->positions_available : 1) }}"
                        class="marketplace-input">
                    @if($editing)
                        <p class="mt-1.5 text-xs text-slate-500">Currently hired: {{ $job->hired_count }}</p>
                    @else
                        <p class="mt-1.5 text-xs text-slate-500">Choose how many freelancers you intend to hire.</p>
                    @endif
                    @error('positions_available')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="marketplace-card p-5 sm:p-6" aria-labelledby="job-budget-heading">
            <div class="mb-5 border-b border-slate-800 pb-4">
                <p class="marketplace-eyebrow">3. Budget</p>
                <h2 id="job-budget-heading" class="mt-1 text-lg font-semibold text-slate-100">Set a realistic budget range</h2>
                <p class="mt-1 text-sm leading-6 text-slate-400">SwiftKudi shows this range on the job card so freelancers can judge fit before opening the listing.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="budget-min" class="marketplace-label">Minimum budget (₦) <span class="text-red-400">*</span></label>
                    <input id="budget-min" type="number" name="budget_min" min="0" max="1000000000" step="100" required
                        value="{{ old('budget_min', $editing ? $job->budget_min : '') }}"
                        class="marketplace-input" placeholder="50000">
                    @error('budget_min')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="budget-max" class="marketplace-label">Maximum budget (₦) <span class="text-red-400">*</span></label>
                    <input id="budget-max" type="number" name="budget_max" min="0" max="1000000000" step="100" required
                        value="{{ old('budget_max', $editing ? $job->budget_max : '') }}"
                        class="marketplace-input" placeholder="100000">
                    @error('budget_max')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="marketplace-card p-5 sm:p-6" aria-labelledby="job-extra-heading">
            <div class="mb-5 border-b border-slate-800 pb-4">
                <p class="marketplace-eyebrow">4. Fit</p>
                <h2 id="job-extra-heading" class="mt-1 text-lg font-semibold text-slate-100">Help the right people self-select</h2>
                <p class="mt-1 text-sm leading-6 text-slate-400">Enter one item per line. SwiftKudi stores these as structured lists for job cards and detail pages.</p>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="job-requirements" class="marketplace-label">Requirements</label>
                    <textarea id="job-requirements" name="requirements" rows="5" maxlength="10000"
                        class="marketplace-input min-h-[140px] resize-y"
                        placeholder="Laravel and PHP experience&#10;Git workflow&#10;Clear written communication">{{ $requirementsValue }}</textarea>
                    @error('requirements')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="job-benefits" class="marketplace-label">Benefits or useful context</label>
                    <textarea id="job-benefits" name="benefits" rows="4" maxlength="10000"
                        class="marketplace-input min-h-[120px] resize-y"
                        placeholder="Flexible schedule&#10;Potential ongoing work">{{ $benefitsValue }}</textarea>
                    @error('benefits')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>
    </div>

    <aside class="space-y-4 lg:sticky lg:top-24">
        <div class="marketplace-card p-5">
            <h2 class="font-semibold text-slate-100">What freelancers will see</h2>
            <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-400">
                <li class="flex gap-2"><i class="fas fa-check mt-1 text-indigo-400"></i><span>Your title, summary and category.</span></li>
                <li class="flex gap-2"><i class="fas fa-check mt-1 text-indigo-400"></i><span>Budget, engagement type and experience level.</span></li>
                <li class="flex gap-2"><i class="fas fa-check mt-1 text-indigo-400"></i><span>Requirements, location and proposal activity supported by the job record.</span></li>
            </ul>
        </div>
        <div class="marketplace-card p-5">
            <h2 class="font-semibold text-slate-100">Before you publish</h2>
            <p class="mt-2 text-sm leading-6 text-slate-400">Avoid adding sensitive information to the public description. You can discuss project-specific details with applicants through SwiftKudi Messages.</p>
        </div>
    </aside>
</div>
