@extends('layouts.app')

@section('title', ($profile->professional_title ? $profile->user->name . ' — ' . $profile->professional_title : $profile->user->name . ' Freelancer') . ' | SwiftKudi')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($profile->bio ?: ($profile->user->name . ' is a freelancer on SwiftKudi.')), 155))
@section('canonical', route('freelancers.show', $profile->slug))
@section('og_type', 'profile')

@push('meta')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ProfilePage',
    'mainEntity' => [
        '@type' => 'Person',
        'name' => $profile->user->name,
        'jobTitle' => $profile->professional_title,
        'description' => $profile->bio,
        'url' => route('freelancers.show', $profile->slug),
        'knowsAbout' => $profile->skills ?? [],
    ],
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container">
        <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500"><a href="{{ route('freelancers.index') }}" class="hover:text-indigo-300">Find Talent</a><i class="fas fa-chevron-right text-[9px]"></i><span>{{ $profile->user->name }}</span></nav>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_330px]">
            <div class="space-y-6 !pt-0 !min-h-0">
                <section class="marketplace-card p-6 sm:p-8">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                        <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-full bg-indigo-600 text-2xl font-bold text-white">{{ strtoupper(substr($profile->user->name, 0, 2)) }}</div>
                        <div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-2"><h1 class="font-heading text-2xl font-bold text-white">{{ $profile->user->name }}</h1>@if($profile->is_available)<span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-300">Available</span>@endif</div><p class="mt-1.5 text-lg text-gray-300">{{ $profile->professional_title ?: 'Independent professional' }}</p><div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-400"><span><i class="fas fa-star mr-1 text-amber-400"></i><strong class="text-gray-200">{{ number_format((float)$profile->average_rating, 1) }}</strong> · {{ $profile->total_reviews }} reviews</span><span><i class="fas fa-check-circle mr-1 text-emerald-400"></i>{{ $profile->total_orders_completed }} completed orders</span>@if($profile->hourly_rate)<span><strong class="text-white">₦{{ number_format((float)$profile->hourly_rate) }}</strong>/hr</span>@endif</div>@if($profile->availability_note)<p class="mt-3 text-sm text-gray-500">{{ $profile->availability_note }}</p>@endif</div>
                    </div>
                    @if($profile->bio)<div class="mt-7 border-t border-dark-700 pt-6"><h2 class="text-base font-semibold text-white">About</h2><p class="mt-3 whitespace-pre-line text-sm leading-7 text-gray-400">{{ $profile->bio }}</p></div>@endif
                </section>

                @if(!empty($profile->skills))<section class="marketplace-card p-6"><h2 class="text-lg font-semibold text-white">Skills</h2><div class="mt-4 flex flex-wrap gap-2">@foreach($profile->skills as $skill)<span class="marketplace-pill">{{ $skill }}</span>@endforeach</div></section>@endif

                @if($services->isNotEmpty())<section><div class="mb-4"><h2 class="text-lg font-semibold text-white">Services</h2><p class="mt-1 text-sm text-gray-500">Ready-to-buy offers from this freelancer.</p></div><div class="grid gap-4 md:grid-cols-2">@foreach($services as $service)<a href="{{ route('professional-services.show', $service) }}" class="marketplace-card-hover block p-5"><div class="flex items-start justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-wide text-indigo-400">{{ optional($service->category)->name ?: 'Service' }}</p><h3 class="mt-2 line-clamp-2 font-semibold text-white">{{ $service->title }}</h3></div><span class="whitespace-nowrap font-bold text-white">₦{{ number_format((float)$service->price) }}</span></div><div class="mt-4 flex items-center justify-between border-t border-dark-700 pt-4 text-xs text-gray-500"><span>{{ $service->delivery_days }} day delivery</span><span>{{ $service->revisions_included }} revisions</span></div></a>@endforeach</div></section>@endif

                @if(!empty($profile->portfolio_links))<section class="marketplace-card p-6"><h2 class="text-lg font-semibold text-white">Portfolio links</h2><div class="mt-4 space-y-2">@foreach($profile->portfolio_links as $link)@php $host = parse_url($link, PHP_URL_HOST) ?: $link; @endphp<a href="{{ $link }}" rel="nofollow noopener noreferrer" target="_blank" class="flex items-center justify-between rounded-lg border border-dark-700 bg-dark-950 px-4 py-3 text-sm text-gray-300 hover:border-indigo-500/40 hover:text-indigo-300"><span class="truncate">{{ $host }}</span><i class="fas fa-arrow-up-right-from-square ml-3 text-xs"></i></a>@endforeach</div></section>@endif

                @if(!empty($profile->work_experience) || !empty($profile->education) || !empty($profile->certifications))<section class="grid gap-4 md:grid-cols-2">@if(!empty($profile->work_experience))<div class="marketplace-card p-5"><h2 class="font-semibold text-white">Experience</h2><ul class="mt-3 space-y-2 text-sm text-gray-400">@foreach($profile->work_experience as $item)<li class="flex gap-2"><i class="fas fa-briefcase mt-1 text-xs text-gray-600"></i><span>{{ $item }}</span></li>@endforeach</ul></div>@endif @if(!empty($profile->education))<div class="marketplace-card p-5"><h2 class="font-semibold text-white">Education</h2><ul class="mt-3 space-y-2 text-sm text-gray-400">@foreach($profile->education as $item)<li class="flex gap-2"><i class="fas fa-graduation-cap mt-1 text-xs text-gray-600"></i><span>{{ $item }}</span></li>@endforeach</ul></div>@endif @if(!empty($profile->certifications))<div class="marketplace-card p-5 md:col-span-2"><h2 class="font-semibold text-white">Certifications</h2><div class="mt-3 flex flex-wrap gap-2">@foreach($profile->certifications as $item)<span class="marketplace-pill"><i class="fas fa-certificate mr-1 text-indigo-400"></i>{{ $item }}</span>@endforeach</div></div>@endif</section>@endif
            </div>

            <aside class="space-y-5">
                <section class="marketplace-card sticky top-24 p-5"><p class="text-sm font-semibold text-white">Work with {{ explode(' ', $profile->user->name)[0] }}</p>@if($profile->hourly_rate)<p class="mt-2 text-2xl font-bold text-white">₦{{ number_format((float)$profile->hourly_rate) }}<span class="text-sm font-normal text-gray-500">/hr</span></p>@endif
                    @auth
                        @if(auth()->id() !== $profile->user_id)<button type="button" onclick="showContactModal()" class="marketplace-btn-primary mt-5 w-full"><i class="far fa-comment"></i>Message freelancer</button>@else<a href="{{ route('professional-services.edit-profile') }}" class="marketplace-btn-primary mt-5 w-full"><i class="fas fa-pen"></i>Edit profile</a>@endif
                    @else<a href="{{ route('login') }}" class="marketplace-btn-primary mt-5 w-full">Log in to contact</a>@endauth
                    <a href="{{ route('professional-services.index', ['search' => $profile->professional_title]) }}" class="marketplace-btn-secondary mt-2 w-full">Browse services</a>
                    <div class="mt-5 border-t border-dark-700 pt-5 text-sm"><div class="flex justify-between"><span class="text-gray-500">Profile completeness</span><span class="font-semibold text-gray-300">{{ $profile->profile_completion }}%</span></div><div class="mt-2 h-1.5 rounded-full bg-dark-800"><div class="h-full rounded-full bg-indigo-500" style="width:{{ $profile->profile_completion }}%"></div></div>@if(!empty($profile->languages))<div class="mt-5"><p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Languages</p><p class="mt-2 text-sm text-gray-300">{{ implode(', ', $profile->languages) }}</p></div>@endif</div>
                </section>
            </aside>
        </div>
    </div>
</div>

@auth
@if(auth()->id() !== $profile->user_id)
<div id="contact-modal" class="fixed inset-0 z-[300] hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-2xl border border-dark-700 bg-dark-900 shadow-2xl"><div class="flex items-center justify-between border-b border-dark-700 px-6 py-5"><div><h2 class="font-semibold text-white">Message {{ $profile->user->name }}</h2><p class="mt-1 text-xs text-gray-500">Keep project details clear and professional.</p></div><button type="button" onclick="hideContactModal()" class="p-2 text-gray-500 hover:text-white"><i class="fas fa-times"></i></button></div><form id="contact-form" class="space-y-4 p-6"><input type="hidden" name="recipient_id" value="{{ $profile->user_id }}"><div><label class="marketplace-label">Subject</label><input name="subject" class="marketplace-input" required minlength="3" maxlength="255" placeholder="Project inquiry"></div><div><label class="marketplace-label">Message</label><textarea name="message" rows="6" class="marketplace-input" required minlength="10" maxlength="5000" placeholder="Describe the work, timeline and what you need help with."></textarea></div><div id="contact-feedback" class="hidden rounded-lg border px-3 py-2 text-sm"></div><div class="flex justify-end gap-2"><button type="button" onclick="hideContactModal()" class="marketplace-btn-secondary">Cancel</button><button type="submit" class="marketplace-btn-primary">Send message</button></div></form></div>
</div>
@push('scripts')
<script>
function showContactModal(){const m=document.getElementById('contact-modal');if(m){m.classList.remove('hidden');m.classList.add('flex');}}
function hideContactModal(){const m=document.getElementById('contact-modal');if(m){m.classList.add('hidden');m.classList.remove('flex');}}
document.getElementById('contact-form')?.addEventListener('submit', async function(e){e.preventDefault();const form=this, btn=form.querySelector('button[type=submit]'), box=document.getElementById('contact-feedback');btn.disabled=true;try{const res=await fetch(@json(route('professional-services.contact')),{method:'POST',body:new FormData(form),headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'}});const data=await res.json();box.classList.remove('hidden','border-red-500/20','bg-red-500/10','text-red-300','border-emerald-500/20','bg-emerald-500/10','text-emerald-300');if(res.ok&&data.success){box.classList.add('border-emerald-500/20','bg-emerald-500/10','text-emerald-300');box.textContent=data.message||'Message sent.';form.reset();}else{box.classList.add('border-red-500/20','bg-red-500/10','text-red-300');box.textContent=data.message||'Could not send the message.';}}catch(_){box.classList.remove('hidden');box.classList.add('border-red-500/20','bg-red-500/10','text-red-300');box.textContent='Could not send the message. Please retry.';}finally{btn.disabled=false;}});
</script>
@endpush
@endif
@endauth
@endsection
