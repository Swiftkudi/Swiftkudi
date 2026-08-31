@extends('layouts.app')

@section('title', 'Edit Freelancer Profile | SwiftKudi')
@section('robots', 'noindex,nofollow')

@section('content')
@php
    $skillsArray = is_array($profile->skills) ? $profile->skills : (json_decode($profile->skills, true) ?: []);
    $portfolioArray = is_array($profile->portfolio_links) ? $profile->portfolio_links : (json_decode($profile->portfolio_links, true) ?: []);
    $certsArray = is_array($profile->certifications) ? $profile->certifications : (json_decode($profile->certifications, true) ?: []);
@endphp
<div class="marketplace-page">
    <div class="marketplace-container max-w-6xl">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">Professional identity</p>
                <h1 class="marketplace-title">Edit freelancer profile</h1>
                <p class="marketplace-subtitle">Keep the information clients use to compare talent clear, current and evidence-based.</p>
            </div>
            @if($profile->slug)
                <a href="{{ route('freelancers.show', $profile->slug) }}" class="marketplace-btn-secondary"><i class="far fa-eye"></i>View public profile</a>
            @endif
        </div>

        @include('professional-services.partials.workspace-nav', ['activeWorkspace' => 'profile'])

        <div id="profile-feedback" class="hidden marketplace-card mb-5 border-red-500/40 bg-red-500/5 p-4 text-sm text-red-200" role="alert" aria-live="polite"></div>

        <form id="profile-form" action="{{ route('professional-services.update-profile') }}" method="POST" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-start" novalidate>
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <section class="marketplace-card p-5 sm:p-6">
                    <div class="mb-5 border-b border-slate-800 pb-4"><p class="marketplace-eyebrow">Positioning</p><h2 class="mt-1 text-lg font-semibold text-white">Professional overview</h2><p class="mt-1 text-sm leading-6 text-slate-400">Tell clients what you do, what kind of work you accept and the rate information already supported by SwiftKudi.</p></div>
                    <div class="space-y-5">
                        <div><label for="professional_title" class="marketplace-label">Professional title <span class="text-red-400">*</span></label><input type="text" name="professional_title" id="professional_title" value="{{ old('professional_title', $profile->professional_title) }}" minlength="3" maxlength="160" required class="marketplace-input" placeholder="e.g. Laravel & Flutter Developer"></div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/35 p-4"><label class="flex cursor-pointer items-start gap-3"><input type="checkbox" name="is_available" value="1" {{ old('is_available', $profile->is_available) ? 'checked' : '' }} class="mt-1 h-5 w-5 rounded border-slate-600 bg-slate-900 text-indigo-600 focus:ring-indigo-500"><span><span class="font-semibold text-slate-200">Available for work</span><span class="mt-1 block text-sm leading-6 text-slate-500">Show clients that you are currently accepting new projects.</span></span></label></div>
                        <div><label for="availability_note" class="marketplace-label">Availability note</label><input type="text" name="availability_note" id="availability_note" value="{{ old('availability_note', $profile->availability_note) }}" maxlength="255" class="marketplace-input" placeholder="e.g. Available up to 30 hrs/week"></div>
                        <div><label for="hourly_rate" class="marketplace-label">Hourly rate (₦)</label><input type="number" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate', $profile->hourly_rate) }}" min="0" max="100000000" step="0.01" class="marketplace-input" placeholder="5000"><p class="mt-1.5 text-xs text-slate-500">Shown only as the profile rate already supported by the marketplace.</p></div>
                        <div><div class="flex items-end justify-between gap-3"><label for="bio" class="marketplace-label">Professional bio <span class="text-red-400">*</span></label><span class="mb-2 text-xs text-slate-500">40–3,000 characters</span></div><textarea name="bio" id="bio" rows="8" required minlength="40" maxlength="3000" class="marketplace-input min-h-[200px] resize-y" placeholder="Focus on the outcomes you deliver, your experience and the types of clients you help.">{{ old('bio', $profile->bio) }}</textarea></div>
                    </div>
                </section>

                <section class="marketplace-card p-5 sm:p-6">
                    <div class="mb-5 border-b border-slate-800 pb-4"><p class="marketplace-eyebrow">Expertise</p><h2 class="mt-1 text-lg font-semibold text-white">Skills and background</h2><p class="mt-1 text-sm leading-6 text-slate-400">Use concise, relevant information that clients can scan quickly.</p></div>
                    <div class="space-y-5">
                        <div>
                            <label for="skill-input" class="marketplace-label">Skills</label>
                            <div id="skills-container" class="mb-3 flex flex-wrap gap-2">
                                @foreach($skillsArray as $skill)
                                    <span class="marketplace-chip skill-chip"><span class="skill-label">{{ $skill }}</span><button type="button" class="remove-skill text-slate-400 hover:text-white" aria-label="Remove {{ $skill }}"><i class="fas fa-times text-xs"></i></button></span>
                                @endforeach
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row"><input type="text" id="skill-input" maxlength="80" class="marketplace-input flex-1" placeholder="e.g. Laravel"><button type="button" id="add-skill" class="marketplace-btn-secondary shrink-0"><i class="fas fa-plus"></i>Add skill</button></div>
                            <input type="hidden" name="skills" id="skills-input">
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div><label for="languages" class="marketplace-label">Languages</label><input type="text" name="languages" id="languages" value="{{ old('languages', implode(', ', $profile->languages ?? [])) }}" maxlength="1000" class="marketplace-input" placeholder="English, Yoruba"></div>
                            <div><label for="education" class="marketplace-label">Education / training</label><input type="text" name="education" id="education" value="{{ old('education', implode(', ', $profile->education ?? [])) }}" maxlength="2000" class="marketplace-input" placeholder="Degree, school or relevant training"></div>
                        </div>
                        <div><label for="work_experience" class="marketplace-label">Work experience</label><textarea name="work_experience" id="work_experience" rows="5" maxlength="5000" class="marketplace-input resize-y" placeholder="Add relevant roles or experience, one per line.">{{ old('work_experience', implode("\n", $profile->work_experience ?? [])) }}</textarea></div>
                    </div>
                </section>

                <section class="marketplace-card p-5 sm:p-6">
                    <div class="mb-5 border-b border-slate-800 pb-4"><p class="marketplace-eyebrow">Evidence</p><h2 class="mt-1 text-lg font-semibold text-white">Portfolio and certifications</h2><p class="mt-1 text-sm leading-6 text-slate-400">Only add links and credentials you are comfortable showing publicly.</p></div>
                    <div class="space-y-6">
                        <div>
                            <div class="flex items-center justify-between gap-3"><label class="marketplace-label mb-0">Portfolio links</label><button type="button" id="add-portfolio" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200"><i class="fas fa-plus mr-1"></i>Add link</button></div>
                            <div id="portfolio-container" class="mt-3 space-y-3">
                                @foreach($portfolioArray as $link)
                                    <div class="portfolio-row flex gap-2"><input type="url" class="portfolio-link-input marketplace-input flex-1" value="{{ $link }}" maxlength="2048" placeholder="https://example.com/project"><button type="button" class="remove-portfolio marketplace-btn-secondary !px-3 text-red-300" aria-label="Remove portfolio link"><i class="fas fa-trash"></i></button></div>
                                @endforeach
                            </div>
                            <input type="hidden" name="portfolio_links" id="portfolio-input">
                        </div>
                        <div>
                            <div class="flex items-center justify-between gap-3"><label class="marketplace-label mb-0">Certifications</label><button type="button" id="add-certification" class="text-sm font-semibold text-indigo-300 hover:text-indigo-200"><i class="fas fa-plus mr-1"></i>Add certification</button></div>
                            <div id="certifications-container" class="mt-3 space-y-3">
                                @foreach($certsArray as $cert)
                                    <div class="certification-row flex gap-2"><input type="text" class="certification-input marketplace-input flex-1" value="{{ $cert }}" maxlength="160" placeholder="Certification or credential"><button type="button" class="remove-certification marketplace-btn-secondary !px-3 text-red-300" aria-label="Remove certification"><i class="fas fa-trash"></i></button></div>
                                @endforeach
                            </div>
                            <input type="hidden" name="certifications" id="certifications-input">
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-24">
                <div class="marketplace-card p-5">
                    <div class="flex items-center justify-between gap-3"><h2 class="font-semibold text-white">Profile completeness</h2><span class="font-semibold text-indigo-300">{{ $profile->profile_completion }}%</span></div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-800"><div class="h-full rounded-full bg-indigo-600" style="width: {{ max(0, min(100, $profile->profile_completion)) }}%"></div></div>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Completeness is calculated from real profile fields: title, bio, skills, rate, languages and portfolio links.</p>
                </div>
                <div class="marketplace-card p-5"><h2 class="font-semibold text-white">Public trust</h2><p class="mt-2 text-sm leading-6 text-slate-500">Ratings, reviews, completed work and verification are shown only when the platform has those records. This editor does not invent reputation signals.</p></div>
                <button type="submit" class="marketplace-btn-primary w-full"><i class="fas fa-save"></i>Save profile</button>
            </aside>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('profile-form');
    const feedback = document.getElementById('profile-feedback');
    const skillsContainer = document.getElementById('skills-container');
    const skillInput = document.getElementById('skill-input');
    const portfolioContainer = document.getElementById('portfolio-container');
    const certificationsContainer = document.getElementById('certifications-container');

    function showError(message) {
        feedback.textContent = message;
        feedback.classList.remove('hidden');
        feedback.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function updateSkillsInput() {
        document.getElementById('skills-input').value = JSON.stringify(Array.from(skillsContainer.querySelectorAll('.skill-label')).map((node) => node.textContent.trim()).filter(Boolean));
    }
    function updatePortfolioInput() {
        document.getElementById('portfolio-input').value = JSON.stringify(Array.from(document.querySelectorAll('.portfolio-link-input')).map((input) => input.value.trim()).filter(Boolean));
    }
    function updateCertificationsInput() {
        document.getElementById('certifications-input').value = JSON.stringify(Array.from(document.querySelectorAll('.certification-input')).map((input) => input.value.trim()).filter(Boolean));
    }

    function bindSkillRemove(button) { button.addEventListener('click', () => { button.closest('.skill-chip').remove(); updateSkillsInput(); }); }
    document.querySelectorAll('.remove-skill').forEach(bindSkillRemove);

    document.getElementById('add-skill').addEventListener('click', () => {
        const value = skillInput.value.trim();
        if (!value || skillsContainer.querySelectorAll('.skill-chip').length >= 30) return;
        const existing = Array.from(skillsContainer.querySelectorAll('.skill-label')).some((node) => node.textContent.trim().toLowerCase() === value.toLowerCase());
        if (existing) { skillInput.value = ''; return; }
        const chip = document.createElement('span');
        chip.className = 'marketplace-chip skill-chip';
        const label = document.createElement('span');
        label.className = 'skill-label';
        label.textContent = value;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'remove-skill text-slate-400 hover:text-white';
        button.setAttribute('aria-label', 'Remove skill');
        button.innerHTML = '<i class="fas fa-times text-xs"></i>';
        chip.append(label, button);
        skillsContainer.appendChild(chip);
        bindSkillRemove(button);
        skillInput.value = '';
        updateSkillsInput();
    });
    skillInput.addEventListener('keydown', (event) => { if (event.key === 'Enter') { event.preventDefault(); document.getElementById('add-skill').click(); } });

    function bindPortfolioRemove(button) { button.addEventListener('click', () => { button.closest('.portfolio-row').remove(); updatePortfolioInput(); }); }
    document.querySelectorAll('.remove-portfolio').forEach(bindPortfolioRemove);
    document.getElementById('add-portfolio').addEventListener('click', () => {
        if (portfolioContainer.children.length >= 10) return;
        const row = document.createElement('div'); row.className = 'portfolio-row flex gap-2';
        row.innerHTML = '<input type="url" class="portfolio-link-input marketplace-input flex-1" maxlength="2048" placeholder="https://example.com/project"><button type="button" class="remove-portfolio marketplace-btn-secondary !px-3 text-red-300" aria-label="Remove portfolio link"><i class="fas fa-trash"></i></button>';
        portfolioContainer.appendChild(row); bindPortfolioRemove(row.querySelector('.remove-portfolio')); row.querySelector('input').addEventListener('input', updatePortfolioInput);
    });

    function bindCertificationRemove(button) { button.addEventListener('click', () => { button.closest('.certification-row').remove(); updateCertificationsInput(); }); }
    document.querySelectorAll('.remove-certification').forEach(bindCertificationRemove);
    document.getElementById('add-certification').addEventListener('click', () => {
        if (certificationsContainer.children.length >= 20) return;
        const row = document.createElement('div'); row.className = 'certification-row flex gap-2';
        row.innerHTML = '<input type="text" class="certification-input marketplace-input flex-1" maxlength="160" placeholder="Certification or credential"><button type="button" class="remove-certification marketplace-btn-secondary !px-3 text-red-300" aria-label="Remove certification"><i class="fas fa-trash"></i></button>';
        certificationsContainer.appendChild(row); bindCertificationRemove(row.querySelector('.remove-certification')); row.querySelector('input').addEventListener('input', updateCertificationsInput);
    });

    document.querySelectorAll('.portfolio-link-input').forEach((input) => input.addEventListener('input', updatePortfolioInput));
    document.querySelectorAll('.certification-input').forEach((input) => input.addEventListener('input', updateCertificationsInput));
    updateSkillsInput(); updatePortfolioInput(); updateCertificationsInput();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        feedback.classList.add('hidden'); feedback.textContent = '';
        updateSkillsInput(); updatePortfolioInput(); updateCertificationsInput();
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const button = form.querySelector('button[type="submit"]');
        const original = button.innerHTML; button.disabled = true; button.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>Saving…';
        try {
            const response = await fetch(form.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: new FormData(form) });
            const data = await response.json().catch(() => ({ message: 'Unexpected server response.' }));
            if (response.ok && data.success) {
                if (data.next_step_redirect) { window.location.href = data.next_step_redirect; return; }
                window.location.reload(); return;
            }
            let message = data.message || 'We could not save your profile.';
            if (data.errors) message += ' ' + Object.values(data.errors).flat().join(' ');
            showError(message);
        } catch (error) { console.error(error); showError('Network error. Please check your connection and try again.'); }
        finally { button.disabled = false; button.innerHTML = original; }
    });
})();
</script>
@endpush
@endsection
