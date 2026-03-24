@extends('layouts.admin')

@section('title', 'Withdrawals')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Manage Withdrawals</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Process and view withdrawal requests</p>
            </div>
            <a href="{{ route('admin.index') }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 dark:text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $pendingWithdrawals }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Completed</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ \App\Models\Withdrawal::completed()->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center">
                        <i class="fas fa-naira-sign text-indigo-600 dark:text-indigo-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Paid</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            ₦{{ number_format(\App\Models\Withdrawal::completed()->sum('amount'), 2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdrawals Table -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 overflow-hidden">
            <form id="bulk-form-withdrawals" action="{{ route('admin.withdrawals.bulk-delete') }}" method="POST">@csrf</form>
            <div id="bulk-toolbar-withdrawals" class="hidden px-6 py-3 bg-red-100 dark:bg-red-500/10 border-b border-red-200 dark:border-red-500/20 flex items-center justify-between">
                <span class="text-sm text-red-600 dark:text-red-400 font-medium"><span id="bulk-count-withdrawals">0</span> selected</span>
                <button type="button" onclick="submitBulkDelete('withdrawals')" class="px-4 py-1.5 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-500/30 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-trash mr-2"></i>Delete Selected
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                    <thead class="bg-gray-50 dark:bg-dark-800">
                        <tr>
                            <th class="px-4 py-4 w-10">
                                <input type="checkbox" id="select-all-withdrawals" class="bulk-select-all w-4 h-4 rounded cursor-pointer" data-target="bulk-cb-withdrawals">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Bank Details</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-dark-900 divide-y divide-gray-200 dark:divide-dark-700">
                        @forelse($withdrawals as $withdrawal)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-800 transition-colors">
                            <td class="px-4 py-4">
                                <input type="checkbox" name="ids[]" value="{{ $withdrawal->id }}" class="bulk-cb-withdrawals w-4 h-4 rounded cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr($withdrawal->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $withdrawal->user->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $withdrawal->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-green-600 dark:text-green-400">₦{{ number_format($withdrawal->amount, 2) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $withdrawal->bank_name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $withdrawal->account_number }} - {{ $withdrawal->account_name }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($withdrawal->status === 'pending')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                </span>
                                @elseif($withdrawal->status === 'completed')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">
                                    <i class="fas fa-check-circle mr-1"></i>Completed
                                </span>
                                @elseif($withdrawal->status === 'rejected')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400">
                                    <i class="fas fa-times-circle mr-1"></i>Rejected
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $withdrawal->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @if($withdrawal->status === 'pending')
                                    <form action="{{ route('admin.approve-withdrawal', $withdrawal) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-500/30 rounded-lg text-xs font-semibold transition-colors">
                                            <i class="fas fa-check mr-1"></i>Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.reject-withdrawal', $withdrawal) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-500/30 rounded-lg text-xs font-semibold transition-colors">
                                            <i class="fas fa-times mr-1"></i>Reject
                                        </button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.withdrawals.delete', $withdrawal) }}" onsubmit="return confirm('Delete this withdrawal record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 bg-gray-100 dark:bg-gray-500/20 text-gray-600 dark:text-gray-400 hover:bg-red-100 dark:hover:bg-red-500/20 hover:text-red-600 dark:hover:text-red-400 rounded-lg text-xs font-semibold transition-colors" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-money-bill-wave text-gray-400 text-2xl"></i>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400">No withdrawals found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-700">
                {{ $withdrawals->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
