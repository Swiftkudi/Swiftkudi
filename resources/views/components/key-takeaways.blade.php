{{--
    Key Takeaways Component
    Provides a summary box for AI extraction at the end of articles.
    Usage: 
    <x-key-takeaways>
        <li>First key point</li>
        <li>Second key point</li>
    </x-key-takeaways>
--}}

@props(['title' => 'Key Takeaways'])

<div class="key-takeaways" itemscope itemtype="https://schema.org/Article">
    <div class="takeaways-header">
        <span class="takeaways-icon">📌</span>
        <h3 class="takeaways-title">{{ $title }}</h3>
    </div>
    <ul class="takeaways-list">
        {{ $slot }}
    </ul>
</div>

@push('styles')
<style>
.key-takeaways {
    background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
    border: 1px solid #3b82f6;
    border-radius: 12px;
    padding: 1.5rem;
    margin: 2rem 0;
}
.takeaways-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(59, 130, 246, 0.3);
}
.takeaways-icon {
    font-size: 1.25rem;
}
.takeaways-title {
    color: #60a5fa;
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.takeaways-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.takeaways-list li {
    color: #e2e8f0;
    padding: 0.5rem 0;
    padding-left: 1.5rem;
    position: relative;
    line-height: 1.5;
}
.takeaways-list li::before {
    content: "→";
    position: absolute;
    left: 0;
    color: #60a5fa;
    font-weight: 600;
}
</style>
@endpush
