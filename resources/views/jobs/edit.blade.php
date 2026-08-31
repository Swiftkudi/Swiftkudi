@extends('layouts.app')

@section('title', 'Edit Job - SwiftKudi')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container max-w-6xl">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">Manage job</p>
                <h1 class="marketplace-title">Edit {{ $job->title }}</h1>
                <p class="marketplace-subtitle">Keep the listing accurate so freelancers can make informed proposal decisions.</p>
            </div>
            <a href="{{ route('jobs.show', $job) }}" class="marketplace-btn-secondary"><i class="fas fa-arrow-left"></i> Back to job</a>
        </div>

        <form action="{{ route('jobs.update', $job) }}" method="POST" class="space-y-6" novalidate>
            @csrf
            @method('PUT')
            @include('jobs.partials.job-form-fields', ['job' => $job])

            <div class="marketplace-card flex flex-col-reverse gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <p class="text-sm text-slate-500">Changes are visible on the public listing after you save them.</p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <a href="{{ route('jobs.show', $job) }}" class="marketplace-btn-secondary">Cancel</a>
                    <button type="submit" class="marketplace-btn-primary"><i class="fas fa-save"></i> Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
