{{--
    Answer Block Component
    Provides a concise, extractable answer for AI systems.
    Usage: <x-answer-block>Your direct answer here</x-answer-block>
--}}

@props(['question' => '', 'importance' => 'high'])

<div class="ai-answer-block" role="answer" aria-label="Answer to: {{ $question }}">
    <div class="answer-indicator">
        <span class="badge bg-primary">
            @if($importance === 'high')
                ⭐ Key Answer
            @else
                ✓ Answer
            @endif
        </span>
    </div>
    <div class="answer-content">
        {{ $slot }}
    </div>
</div>

@push('styles')
<style>
.ai-answer-block {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-left: 4px solid #3b82f6;
    border-radius: 8px;
    padding: 1rem 1.5rem;
    margin: 1.5rem 0;
}
.answer-indicator {
    margin-bottom: 0.5rem;
}
.answer-indicator .badge {
    background: #3b82f6;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}
.answer-content {
    color: #f1f5f9;
    font-size: 1rem;
    line-height: 1.6;
}
</style>
@endpush
