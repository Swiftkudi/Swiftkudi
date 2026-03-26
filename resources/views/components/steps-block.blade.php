{{--
    Steps Block Component
    Provides clear, numbered step-by-step instructions for AI extraction.
    Usage: 
    <x-steps-block title="How to post a task">
        <x-step number="1">Create account</x-step>
        <x-step number="2">Post task</x-step>
    </x-steps-block>
--}}

@props(['title' => '', 'totalSteps' => 0])

<div class="steps-block" itemscope itemtype="https://schema.org/HowTo">
    @if($title)
    <h3 class="steps-title" itemprop="name">{{ $title }}</h3>
    @endif
    <ol class="steps-list">
        {{ $slot }}
    </ol>
</div>

@push('styles')
<style>
.steps-block {
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 1.5rem;
    margin: 1.5rem 0;
}
.steps-title {
    color: #f1f5f9;
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #334155;
}
.steps-list {
    list-style: none;
    padding: 0;
    margin: 0;
    counter-reset: steps;
}
.steps-list > li {
    counter-increment: steps;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #1e293b;
}
.steps-list > li:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.steps-list > li::before {
    content: counter(steps);
    background: #3b82f6;
    color: white;
    min-width: 1.75rem;
    height: 1.75rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}
.step-content {
    color: #e2e8f0;
    line-height: 1.6;
    flex: 1;
}
.step-content strong {
    color: #f1f5f9;
}
</style>
@endpush
