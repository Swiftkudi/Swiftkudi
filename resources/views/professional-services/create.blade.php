@extends('layouts.app')

@section('title', 'Create Service - SwiftKudi')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container max-w-6xl">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">Sell a service</p>
                <h1 class="marketplace-title">Create a professional service</h1>
                <p class="marketplace-subtitle">Package a clear outcome, price and delivery promise so clients can understand exactly what they are purchasing.</p>
            </div>
            <a href="{{ route('professional-services.my-services') }}" class="marketplace-btn-secondary"><i class="fas fa-arrow-left"></i> My services</a>
        </div>

        <form id="serviceForm" class="space-y-6" novalidate>
            @csrf

            <div id="service-create-error-box" class="hidden marketplace-card border-red-500/40 bg-red-500/5 p-4 text-sm text-red-200" role="alert" aria-live="polite"></div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-start">
                <div class="space-y-6">
                    <section class="marketplace-card p-5 sm:p-6" aria-labelledby="service-scope-heading">
                        <div class="mb-5 border-b border-slate-800 pb-4">
                            <p class="marketplace-eyebrow">1. Service scope</p>
                            <h2 id="service-scope-heading" class="mt-1 text-lg font-semibold text-slate-100">Describe the outcome</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-400">Make the title easy to scan and explain what the client receives in practical terms.</p>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label for="service-title" class="marketplace-label">Service title <span class="text-red-400">*</span></label>
                                <input id="service-title" type="text" name="title" required minlength="5" maxlength="255"
                                    class="marketplace-input" placeholder="e.g. Build and deploy a Laravel business website">
                                <p class="mt-1.5 text-xs text-slate-500">Use an outcome-oriented title rather than a generic skill name.</p>
                            </div>

                            <div>
                                <label for="service-category" class="marketplace-label">Category <span class="text-red-400">*</span></label>
                                <select id="service-category" name="category_id" required class="marketplace-input">
                                    <option value="">Select a category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <div class="flex items-end justify-between gap-4">
                                    <label for="service-description" class="marketplace-label">Description <span class="text-red-400">*</span></label>
                                    <span class="mb-2 text-xs text-slate-500">20–5,000 characters</span>
                                </div>
                                <textarea id="service-description" name="description" required minlength="20" maxlength="5000" rows="9"
                                    class="marketplace-input min-h-[220px] resize-y"
                                    placeholder="Explain what is included, the process, what you need from the client, and the final deliverable."></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="marketplace-card p-5 sm:p-6" aria-labelledby="service-pricing-heading">
                        <div class="mb-5 border-b border-slate-800 pb-4">
                            <p class="marketplace-eyebrow">2. Price & delivery</p>
                            <h2 id="service-pricing-heading" class="mt-1 text-lg font-semibold text-slate-100">Set clear purchase terms</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-400">Clients see these values before ordering. Orders use SwiftKudi’s existing wallet and escrow flow.</p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-3">
                            <div>
                                <label for="service-price" class="marketplace-label">Starting price (₦) <span class="text-red-400">*</span></label>
                                <input id="service-price" type="number" name="price" required min="100" max="1000000000" step="0.01" class="marketplace-input" placeholder="50000">
                            </div>
                            <div>
                                <label for="delivery-days" class="marketplace-label">Delivery days <span class="text-red-400">*</span></label>
                                <input id="delivery-days" type="number" name="delivery_days" required min="1" max="30" class="marketplace-input" placeholder="7">
                            </div>
                            <div>
                                <label for="revisions" class="marketplace-label">Revisions <span class="text-red-400">*</span></label>
                                <input id="revisions" type="number" name="revisions_included" required min="0" max="5" value="1" class="marketplace-input">
                            </div>
                        </div>
                    </section>

                    <section class="marketplace-card p-5 sm:p-6" aria-labelledby="service-proof-heading">
                        <div class="mb-5 border-b border-slate-800 pb-4">
                            <p class="marketplace-eyebrow">3. Proof</p>
                            <h2 id="service-proof-heading" class="mt-1 text-lg font-semibold text-slate-100">Add relevant portfolio references</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-400">Optional. Add up to three valid web links that help clients understand related work. Only links you provide are shown.</p>
                        </div>

                        <div class="grid gap-4">
                            @for($i = 0; $i < 3; $i++)
                                <div>
                                    <label for="portfolio-link-{{ $i }}" class="marketplace-label">Portfolio URL {{ $i + 1 }}</label>
                                    <input id="portfolio-link-{{ $i }}" type="url" name="portfolio_links[]" maxlength="2048" class="marketplace-input" placeholder="https://example.com/project">
                                </div>
                            @endfor
                        </div>
                    </section>

                    <section class="marketplace-card p-5 sm:p-6" aria-labelledby="service-addons-heading">
                        <div class="mb-5 flex flex-col gap-3 border-b border-slate-800 pb-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="marketplace-eyebrow">4. Optional extras</p>
                                <h2 id="service-addons-heading" class="mt-1 text-lg font-semibold text-slate-100">Offer useful add-ons</h2>
                                <p class="mt-1 text-sm leading-6 text-slate-400">Add only extras that genuinely extend the base service. You can add up to 10.</p>
                            </div>
                            <button type="button" id="addAddon" class="marketplace-btn-secondary shrink-0"><i class="fas fa-plus"></i> Add extra</button>
                        </div>

                        <div id="addonsContainer" class="space-y-4"></div>
                        <div id="addonsEmpty" class="rounded-xl border border-dashed border-slate-700 px-4 py-6 text-center text-sm text-slate-500">No extras added. The base service can be purchased on its own.</div>
                    </section>
                </div>

                <aside class="space-y-4 lg:sticky lg:top-24">
                    <div class="marketplace-card p-5">
                        <h2 class="font-semibold text-slate-100">What clients need to know</h2>
                        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-400">
                            <li class="flex gap-2"><i class="fas fa-check mt-1 text-indigo-400"></i><span>What they are buying.</span></li>
                            <li class="flex gap-2"><i class="fas fa-check mt-1 text-indigo-400"></i><span>How much it costs and when it will be delivered.</span></li>
                            <li class="flex gap-2"><i class="fas fa-check mt-1 text-indigo-400"></i><span>What revisions and optional extras are included.</span></li>
                        </ul>
                    </div>
                    <div class="marketplace-card p-5">
                        <h2 class="font-semibold text-slate-100">Review status</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-400">New services use the existing SwiftKudi moderation workflow and are submitted for admin review before becoming active.</p>
                    </div>
                </aside>
            </div>

            <div class="marketplace-card flex flex-col-reverse gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <p class="text-sm text-slate-500">You can manage the service after it has been created.</p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <a href="{{ route('professional-services.my-services') }}" class="marketplace-btn-secondary">Cancel</a>
                    <button id="serviceSubmit" type="submit" class="marketplace-btn-primary"><i class="fas fa-paper-plane"></i> Create service</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('serviceForm');
    const submitBtn = document.getElementById('serviceSubmit');
    const errorBox = document.getElementById('service-create-error-box');
    const addonsContainer = document.getElementById('addonsContainer');
    const addonsEmpty = document.getElementById('addonsEmpty');
    const addAddonBtn = document.getElementById('addAddon');
    let addonCount = 0;

    function showFallbackError(message) {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearErrors() {
        errorBox.textContent = '';
        errorBox.classList.add('hidden');
        if (window.SwiftkudiFormFeedback && typeof window.SwiftkudiFormFeedback.clearValidationErrors === 'function') {
            window.SwiftkudiFormFeedback.clearValidationErrors(form);
        }
    }

    function refreshAddonEmptyState() {
        addonsEmpty.classList.toggle('hidden', addonsContainer.children.length > 0);
    }

    addAddonBtn.addEventListener('click', function () {
        if (addonsContainer.children.length >= 10) return;
        const index = addonCount++;
        const wrapper = document.createElement('div');
        wrapper.className = 'rounded-xl border border-slate-800 bg-slate-950/35 p-4';
        wrapper.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <h3 class="text-sm font-semibold text-slate-200">Optional extra</h3>
                <button type="button" class="remove-addon inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:border-red-400/60 hover:text-red-300" aria-label="Remove extra"><i class="fas fa-times"></i></button>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="marketplace-label">Name</label>
                    <input type="text" name="addons[${index}][name]" maxlength="120" required class="marketplace-input" placeholder="e.g. Priority delivery">
                </div>
                <div>
                    <label class="marketplace-label">Extra price (₦)</label>
                    <input type="number" name="addons[${index}][price]" min="0" max="1000000000" step="0.01" required class="marketplace-input" placeholder="10000">
                </div>
                <div class="sm:col-span-2">
                    <label class="marketplace-label">Description</label>
                    <textarea name="addons[${index}][description]" maxlength="500" rows="2" class="marketplace-input resize-y" placeholder="Explain what this extra adds to the base service."></textarea>
                </div>
                <div>
                    <label class="marketplace-label">Extra delivery days</label>
                    <input type="number" name="addons[${index}][delivery_days_extra]" min="0" max="30" value="0" class="marketplace-input">
                </div>
            </div>`;
        wrapper.querySelector('.remove-addon').addEventListener('click', function () {
            wrapper.remove();
            refreshAddonEmptyState();
        });
        addonsContainer.appendChild(wrapper);
        refreshAddonEmptyState();
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearErrors();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Creating…';

        try {
            const response = await fetch('{{ route("professional-services.store") }}', {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const contentType = response.headers.get('content-type') || '';
            const data = contentType.includes('application/json') ? await response.json() : { message: 'Unexpected server response.' };

            if (response.ok && data.success) {
                window.location.href = data.next_step_redirect || data.redirect;
                return;
            }

            if (window.SwiftkudiFormFeedback && (response.status === 422 || data.errors || data.error_list)) {
                window.SwiftkudiFormFeedback.showValidationErrors(form, data, { boxId: 'service-create-error-box' });
            } else {
                showFallbackError(data.message || 'We could not create your service. Please review the form and try again.');
            }
        } catch (error) {
            console.error(error);
            showFallbackError('Network error. Please check your connection and try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    });
})();
</script>
@endsection
