@extends('layouts.app')

@section('title', 'Saved Task - Pay to Create')

@section('content')
<div class="max-w-3xl mx-auto py-12">
    <div class="bg-white dark:bg-dark-900 rounded-2xl shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">Saved Task — Complete Payment</h2>

        @php
            $wallet = Auth::user()->wallet;
            $totalBalance = $wallet ? $wallet->withdrawable_balance + $wallet->promo_credit_balance : 0;
            $data = $prefillData ?? [];
            $quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;
            $budget = isset($data['budget']) ? floatval($data['budget']) : 0;
            $perTask = $quantity > 0 ? round($budget / max(1, $quantity), 2) : 0;
            $workerReward = round($perTask * 0.75, 2);
        @endphp

        @if(session('error'))
            <div class="mb-4 p-3 rounded bg-red-50 text-red-800">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-50 text-green-800">{{ session('success') }}</div>
        @endif

        <div class="space-y-4">
            <div>
                <p class="text-sm text-gray-500">Task Title</p>
                <p class="font-semibold">{{ $data['title'] ?? 'Untitled' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Description</p>
                <p>{{ $data['description'] ?? '' }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Category</p>
                    <p class="font-medium">{{ optional($category)->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Platform</p>
                    <p class="font-medium">{{ $data['platform'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Quantity</p>
                    <p class="font-medium">{{ $quantity }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Budget</p>
                    <p class="font-medium">₦{{ number_format($budget, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Per-task (total)</p>
                    <p class="font-medium">₦{{ number_format($perTask, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Worker reward (est.)</p>
                    <p class="font-medium">₦{{ number_format($workerReward, 2) }}</p>
                </div>
            </div>

            <div class="mt-4 p-4 bg-gray-50 dark:bg-dark-800 rounded">
                <p class="text-sm text-gray-500">Your wallet balance</p>
                <p class="text-xl font-semibold">₦{{ number_format($totalBalance, 2) }}</p>
            </div>

            <div class="flex gap-3 justify-end mt-6">
                <a href="{{ route('wallet.deposit') }}" class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-dark-700 text-gray-700">Deposit Funds</a>
                <a href="{{ route('tasks.create.resume') }}" class="px-4 py-2 rounded-lg bg-white border">Edit Details</a>
                <form method="POST" action="{{ route('tasks.create.pay') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold">Pay & Create Task</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
