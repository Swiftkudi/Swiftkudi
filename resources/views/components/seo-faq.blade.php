{{--
    SEO-Optimized FAQ Component
    This component renders FAQs in a way that's optimized for AI answer extraction
    and includes JSON-LD structured data for search engines.
    
    Usage:
    <x-seo-faq :faqs="$faqs" />
    
    Where $faqs is an array of ['question' => '...', 'answer' => '...']
--}}

@props(['faqs' => []])

@if(count($faqs) > 0)
{{-- JSON-LD Structured Data for FAQ Schema --}}
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

{{-- Visual FAQ Section --}}
<section class="faq-section" aria-label="Frequently Asked Questions">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold mb-6">Frequently Asked Questions</h2>
        
        <div class="space-y-4" itemscope itemtype="https://schema.org/FAQPage">
            @foreach($faqs as $faq)
            <div class="faq-item border border-gray-700 rounded-lg overflow-hidden" itemscope itemtype="https://schema.org/Question">
                <details class="group">
                    <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-800 hover:bg-gray-750 transition-colors">
                        <span class="font-semibold text-lg" itemprop="name">{{ $faq['question'] }}</span>
                        <span class="ml-4 flex-shrink-0 text-gray-400 group-open:rotate-180 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </span>
                    </summary>
                    <div class="p-4 bg-gray-900 border-t border-gray-700" itemscope itemtype="https://schema.org/Answer">
                        <p class="text-gray-300" itemprop="text">{{ $faq['answer'] }}</p>
                    </div>
                </details>
            </div>
            @endforeach
        </div>
    </div>
</section>

@push('styles')
<style>
    .faq-section details > summary {
        list-style: none;
    }
    .faq-section details > summary::-webkit-details-marker {
        display: none;
    }
    .faq-section details[open] > summary {
        border-bottom: 1px solid #374151;
    }
</style>
@endpush
@endif
