@extends('layouts.app')

@section('title', 'Task Creator Onboarding')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold">Task Creator Onboarding</h1>
            <p class="text-gray-600">You're now set up to create campaigns and grow your marketplace presence.</p>
        </div>

        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-gray-100 dark:border-dark-700 p-6 mb-4">
            <p class="text-gray-500">To get started, create a campaign using the task creation dashboard and fund it. Once your first campaign is live, you'll see improved visibility.</p>
            <a href="{{ route('tasks.create') }}" class="mt-4 inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-lg">Create Your First Task</a>
        </div>

        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Back to dashboard</a>
    </div>
</div>
@endsection
