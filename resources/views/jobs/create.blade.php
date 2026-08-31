@extends('layouts.app')

@section('title', 'Post a Job - SwiftKudi')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container max-w-6xl">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">Hire talent</p>
                <h1 class="marketplace-title">Post a job</h1>
                <p class="marketplace-subtitle">Create a clear opportunity that qualified freelancers can understand and evaluate quickly.</p>
            </div>
            <a href="{{ route('jobs.index') }}" class="marketplace-btn-secondary"><i class="fas fa-arrow-left"></i> Find Work</a>
        </div>

        <form action="{{ route('jobs.store') }}" method="POST" class="space-y-6" novalidate>
            @csrf
            @include('jobs.partials.job-form-fields')

            <div class="marketplace-card flex flex-col-reverse gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <p class="text-sm text-slate-500">Your job will use the existing SwiftKudi proposal and hiring workflow.</p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <a href="{{ route('jobs.index') }}" class="marketplace-btn-secondary">Cancel</a>
                    <button type="submit" class="marketplace-btn-primary"><i class="fas fa-paper-plane"></i> Post job</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
