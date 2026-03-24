{{--
    Definition Block Component
    Provides clear definitions optimized for AI extraction.
    Usage: <x-definition-block term="Escrow">Money held by a third party...</x-definition-block>
--}}

@props(['term' => '', 'category' => 'general'])

<div class="definition-block" itemscope itemtype="https://schema.org/DefinedTerm">
    <div class="definition-header">
        <span class="definition-term" itemprop="name">{{ $term }}</span>
        @if($category !== 'general')
        <span class="definition-category">{{ $category }}</span>
        @endif
    </div>
    <div class="definition-content" itemprop="description">
        {{ $slot }}
    </div>
    <meta itemprop="inDefinedTermSet" content="Swiftkudi Knowledge Base">
</div>

@push('styles')
<style>
.definition-block {
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 1.25rem;
    margin: 1.5rem 0;
}
.definition-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.definition-term {
    font-weight: 700;
    font-size: 1.125rem;
    color: #60a5fa;
}
.definition-category {
    background: #1e293b;
    color: #94a3b8;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.definition-content {
    color: #e2e8f0;
    line-height: 1.6;
}
</style>
@endpush
