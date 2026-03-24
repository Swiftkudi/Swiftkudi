{{--
    SEO Listing Page Component
    Automatically generates SEO-optimized landing page for marketplace listings
    
    Usage:
    <x-seo-listing 
        :listing="$task"
        type="task"
    />
--}}

@props([
    'listing' => [],
    'type' => 'task', // task, service, product
])

@php
    $seoGenerator = app(\App\Services\SeoGeneratorService::class);
    $seo = $seoGenerator->generateSeoPackage([
        'type' => $type,
        'title' => $listing->title ?? $listing['title'] ?? 'Listing',
        'description' => $listing->description ?? $listing['description'] ?? '',
        'budget' => $listing->budget ?? $listing['price'] ?? null,
        'category' => $listing->category->name ?? $listing['category'] ?? null,
        'slug' => $listing->id ?? $listing['slug'] ?? '1',
        'created_at' => $listing->created_at ?? now(),
    ]);
    
    $faqs = $seo['faq'];
@endphp

{{-- SEO Meta Tags --}}
<title>{{ $seo['seo']['title'] }}</title>
<meta name="description" content="{{ $seo['seo']['meta_description'] }}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ $seo['seo']['canonical_url'] }}">

{{-- Open Graph --}}
<meta property="og:title" content="{{ $seo['seo']['title'] }}">
<meta property="og:description" content="{{ $seo['seo']['meta_description'] }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $seo['seo']['canonical_url'] }}">

{{-- JSON-LD Structured Data --}}
<script type="application/ld+json">
{!! json_encode($seo['schema'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>

{{-- FAQ Schema --}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        @foreach($faqs as $index => $faq)
        {
            "@type": "Question",
            "name": "{{ $faq['question'] }}",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "{{ $faq['answer'] }}"
            }
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>

{{-- Main Content --}}
<article class="seo-listing-page">
    {{-- Hero Section --}}
    <section class="listing-hero">
        <div class="container">
            <h1>{{ $listing->title ?? $listing['title'] }}</h1>
            <p class="listing-type-badge">{{ ucfirst($type) }}</p>
            
            @if(isset($listing->budget) || isset($listing['budget']))
            <div class="listing-budget">
                <span class="label">Budget:</span>
                <span class="amount">₦{{ number_format($listing->budget ?? $listing['budget']) }}</span>
            </div>
            @endif
        </div>
    </section>

    {{-- Description Section --}}
    <section class="listing-description">
        <div class="container">
            <h2>Description</h2>
            <p>{{ $listing->description ?? $listing['description'] }}</p>
        </div>
    </section>

    {{-- Benefits Section --}}
    <section class="listing-benefits">
        <div class="container">
            {!! nl2br(e($seo['content']['benefits'])) !!}
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="listing-faq">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            
            @foreach($faqs as $faq)
            <div class="faq-item">
                <details>
                    <summary>{{ $faq['question'] }}</summary>
                    <div class="answer">{{ $faq['answer'] }}</div>
                </details>
            </div>
            @endforeach
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="listing-cta">
        <div class="container">
            <a href="{{ $seo['seo']['canonical_url'] }}" class="cta-button">
                {{ $type === 'task' ? 'Start This Task' : ($type === 'service' ? 'Hire Now' : 'Buy Now') }}
            </a>
        </div>
    </section>
</article>

@push('styles')
<style>
.seo-listing-page .container {
    max-width: 800px;
    margin: 0 auto;
    padding: 1rem;
}
.seo-listing-page h1 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}
.seo-listing-page h2 {
    font-size: 1.5rem;
    color: #60a5fa;
    margin: 2rem 0 1rem;
}
.listing-type-badge {
    display: inline-block;
    background: #3b82f6;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}
.listing-budget {
    font-size: 1.25rem;
    margin: 1rem 0;
}
.listing-budget .amount {
    color: #10b981;
    font-weight: bold;
}
.faq-item {
    background: #1e293b;
    border-radius: 0.5rem;
    margin: 0.5rem 0;
}
.faq-item summary {
    padding: 1rem;
    cursor: pointer;
    font-weight: 600;
}
.faq-item .answer {
    padding: 1rem;
    padding-top: 0;
    color: #94a3b8;
}
.cta-button {
    display: inline-block;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    padding: 1rem 2rem;
    border-radius: 0.5rem;
    text-decoration: none;
    font-weight: 600;
    margin: 2rem 0;
}
</style>
@endpush
