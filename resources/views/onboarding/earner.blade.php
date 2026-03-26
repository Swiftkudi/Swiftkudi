@extends('layouts.app')

@section('title', 'Earner Onboarding')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold">Earner Onboarding</h1>
            <p class="text-gray-600">Complete these steps to start earning on SwiftKudi.</p>
        </div>

        @if(session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700">{{ session('error') }}</div>
        @endif
        @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-700">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6 mb-6">
            <h2 class="text-xl font-semibold mb-2">Activation Fee</h2>
            <p class="text-gray-500 mb-4">@if($activationEnabled) You need to pay <strong>{{ $currency }}{{ number_format($activationFee, 2) }}</strong> activation fee to start as an earner. @else Activation is currently disabled by admin. @endif</p>

            <div class="grid grid-cols-2 gap-4">
                <div class="border rounded-lg p-3">
                    <p class="text-sm text-gray-500">Wallet Balance</p>
                    <p class="text-2xl font-bold">{{ $currency }}{{ number_format(Auth::user()->wallet_balance, 2) }}</p>
                </div>
                <div class="border rounded-lg p-3">
                    <p class="text-sm text-gray-500">Activation Status</p>
                    <p class="text-2xl font-bold">{{ Auth::user()->activation_paid ? 'Paid' : 'Pending' }}</p>
                </div>
            </div>

            @if($activationEnabled && !Auth::user()->activation_paid)
            <form method="POST" action="{{ route('onboarding.earner.activate') }}" class="mt-4">
                @csrf
                <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white">Pay Activation</button>
            </form>
            @endif
        </div>

        @if($taskCreationEnabled)
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6 mb-6">
            <h2 class="text-xl font-semibold mb-2">Complete Your First Task</h2>
            <p class="text-gray-500 mb-4">Select and complete at least one starter task before finishing onboarding.</p>

            @if(count($sampleTasks))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($sampleTasks as $task)
                <div class="border rounded-lg p-3">
                    <h3 class="font-semibold">{{ $task->title }}</h3>
                    <p class="text-sm text-gray-500">{{ Str::limit($task->description, 70) }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-green-600 font-bold">{{ $currency }}{{ number_format($task->worker_reward_per_task, 2) }}</span>
                        <a href="{{ route('tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-800">Take this task</a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">No sample tasks available. Browse all tasks instead.</p>
            @endif
        </div>
        @endif

        <form method="POST" action="{{ route('onboarding.earner.complete') }}">
            @csrf
            <button type="submit" class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg">Complete Onboarding</button>
        </form>
    </div>
</div>
@endsection
