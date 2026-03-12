@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="py-4 lg:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 lg:mb-8">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Manage Users</h1>
                <p class="text-gray-400 mt-1 text-sm lg:text-base">View and manage all registered users</p>
            </div>
            <a href="{{ route('admin.index') }}" class="inline-flex items-center text-indigo-400 hover:text-indigo-300 text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <!-- Search & Filters -->
        <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6 mb-6 lg:mb-8">
            <form action="{{ route('admin.users') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                        class="w-full px-4 py-2.5 bg-dark-800 border border-dark-600 rounded-xl text-gray-100 focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div class="sm:w-48">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-600 rounded-xl text-gray-100 focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">All Status</option>
                        <option value="activated" {{ request('status') === 'activated' ? 'selected' : '' }}>Activated</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="sm:self-end">
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl shadow-lg shadow-indigo-500/30 text-sm">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Mobile Cards View (visible on mobile only) -->
        <div class="lg:hidden space-y-4">
            <div class="flex items-center justify-between bg-dark-900 border border-dark-700 rounded-xl p-3">
                <p class="text-sm text-gray-300">Select users to delete</p>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="submitBulkClearWalletUsers()" class="px-3 py-1.5 bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30 rounded-lg text-xs font-medium transition-colors">
                        <i class="fas fa-wallet mr-1"></i>Clear Wallet
                    </button>
                    <button type="button" onclick="submitBulkDelete('users')" class="px-3 py-1.5 bg-red-500/20 text-red-400 hover:bg-red-500/30 rounded-lg text-xs font-medium transition-colors">
                        <i class="fas fa-trash mr-1"></i>Delete Selected
                    </button>
                </div>
            </div>
            @foreach($users as $user)
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-medium text-white">{{ $user->name }}</p>
                            <p class="text-sm text-gray-400">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="ids[]" value="{{ $user->id }}" class="bulk-cb-users w-4 h-4 rounded cursor-pointer">
                        @if($user->is_admin)
                        <span class="px-2 py-1 text-xs font-semibold bg-purple-500/20 text-purple-400 rounded-lg">
                            <i class="fas fa-shield-alt mr-1"></i>Admin
                        </span>
                        @endif
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div class="bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Status</p>
                        @if($user->is_suspended)
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400">
                            <i class="fas fa-ban mr-1"></i>Suspended
                        </span>
                        @elseif($user->wallet && $user->wallet->is_activated)
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400">
                            <i class="fas fa-check-circle mr-1"></i>Activated
                        </span>
                        @else
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400">
                            <i class="fas fa-clock mr-1"></i>Pending
                        </span>
                        @endif
                    </div>
                    <div class="bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Level</p>
                        <p class="text-sm font-medium text-white">Level {{ $user->level }}</p>
                    </div>
                    <div class="bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Wallet</p>
                        @if($user->wallet)
                        <p class="text-sm font-medium text-green-400">
                            ₦{{ number_format($user->wallet->withdrawable_balance + $user->wallet->promo_credit_balance, 2) }}
                        </p>
                        @else
                        <p class="text-sm text-gray-500">No wallet</p>
                        @endif
                    </div>
                    <div class="bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Joined</p>
                        <p class="text-sm text-gray-300">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <a href="{{ route('admin.user-details', $user) }}" class="flex-1 flex items-center justify-center px-4 py-2.5 bg-indigo-500/10 text-indigo-400 rounded-xl hover:bg-indigo-500/20 transition-colors text-sm font-medium">
                        <i class="fas fa-eye mr-2"></i>View Details
                    </a>
                    @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="flex-1">
                        @csrf
                        @if($user->is_suspended)
                        <button type="submit" class="w-full px-4 py-2.5 bg-green-500/10 text-green-400 hover:bg-green-500/20 rounded-xl transition-colors text-sm font-medium" onclick="return confirm('Unsuspend this account?')">
                            <i class="fas fa-unlock mr-2"></i>Unsuspend
                        </button>
                        @else
                        <button type="submit" class="w-full px-4 py-2.5 bg-orange-500/10 text-orange-400 hover:bg-orange-500/20 rounded-xl transition-colors text-sm font-medium" onclick="return confirm('Suspend this account?')">
                            <i class="fas fa-ban mr-2"></i>Suspend
                        </button>
                        @endif
                    </form>
                    @endif
                    <form action="{{ route('admin.users.clear-wallet', $user) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2.5 bg-yellow-500/10 text-yellow-400 rounded-xl hover:bg-yellow-500/20 transition-colors text-sm font-medium" onclick="return confirm('Clear all wallet money for this user?')">
                            <i class="fas fa-wallet mr-2"></i>Clear Wallet
                        </button>
                    </form>
                    @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.delete', $user) }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2.5 bg-red-500/10 text-red-400 rounded-xl hover:bg-red-500/20 transition-colors text-sm font-medium" onclick="return confirm('Delete this user account? This action cannot be undone.')">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Desktop Table View (visible on desktop only) -->
        <div class="hidden lg:block bg-dark-900 rounded-2xl shadow-lg border border-dark-700 overflow-hidden">
            <form id="bulk-form-users" action="{{ route('admin.users.bulk-delete') }}" method="POST">@csrf</form>
            <form id="bulk-form-users-wallet" action="{{ route('admin.users.bulk-clear-wallet') }}" method="POST">@csrf</form>
            <div id="bulk-toolbar-users" class="hidden px-6 py-3 bg-red-500/10 border-b border-red-500/20 flex items-center justify-between">
                <span class="text-sm text-red-400 font-medium"><span id="bulk-count-users">0</span> selected</span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="submitBulkClearWalletUsers()" class="px-4 py-1.5 bg-yellow-500/20 text-yellow-300 hover:bg-yellow-500/30 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-wallet mr-2"></i>Clear Wallets
                    </button>
                    <button type="button" onclick="submitBulkDelete('users')" class="px-4 py-1.5 bg-red-500/20 text-red-400 hover:bg-red-500/30 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-trash mr-2"></i>Delete Selected
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-dark-700">
                    <thead class="bg-dark-800">
                        <tr>
                            <th class="px-4 py-4 w-10">
                                <input type="checkbox" id="select-all-users" class="bulk-select-all w-4 h-4 rounded cursor-pointer" data-target="bulk-cb-users">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase">Level</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase">Wallet</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase">Joined</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-dark-900 divide-y divide-dark-700">
                        @foreach($users as $user)
                        <tr class="hover:bg-dark-800 transition-colors">
                            <td class="px-4 py-4">
                                <input type="checkbox" name="ids[]" value="{{ $user->id }}" class="bulk-cb-users w-4 h-4 rounded cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">{{ $user->name }}</p>
                                        <p class="text-sm text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_suspended)
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400">
                                    <i class="fas fa-ban mr-1"></i>Suspended
                                </span>
                                @elseif($user->wallet && $user->wallet->is_activated)
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400">
                                    <i class="fas fa-check-circle mr-1"></i>Activated
                                </span>
                                @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-white">Level {{ $user->level }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->wallet)
                                <span class="text-sm font-medium text-green-400">
                                    ₦{{ number_format($user->wallet->withdrawable_balance + $user->wallet->promo_credit_balance, 2) }}
                                </span>
                                @else
                                <span class="text-gray-500">No wallet</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.user-details', $user) }}" class="p-2 text-indigo-400 hover:bg-dark-700 rounded-lg transition-colors" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.users.clear-wallet', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-yellow-400 hover:bg-dark-700 rounded-lg transition-colors" title="Clear Wallet" onclick="return confirm('Clear all wallet money for this user?')">
                                            <i class="fas fa-wallet"></i>
                                        </button>
                                    </form>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @if($user->is_suspended)
                                        <button type="submit" class="p-2 text-green-400 hover:bg-dark-700 rounded-lg transition-colors" title="Unsuspend User" onclick="return confirm('Unsuspend this account?')">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                        @else
                                        <button type="submit" class="p-2 text-orange-400 hover:bg-dark-700 rounded-lg transition-colors" title="Suspend User" onclick="return confirm('Suspend this account?')">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        @endif
                                    </form>
                                    @endif
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.delete', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-400 hover:bg-dark-700 rounded-lg transition-colors" title="Delete User" onclick="return confirm('Delete this user account? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @if($user->is_admin)
                                    <span class="px-2 py-1 text-xs font-semibold bg-purple-500/20 text-purple-400 rounded-lg">
                                        <i class="fas fa-shield-alt mr-1"></i>Admin
                                    </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
        
        <!-- Mobile Pagination -->
        <div class="lg:hidden mt-4">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function submitBulkClearWalletUsers() {
    var checked = document.querySelectorAll('.bulk-cb-users:checked');
    if (checked.length === 0) {
        alert('Select at least one user.');
        return;
    }
    if (!confirm('Clear wallet money for ' + checked.length + ' selected user(s)?')) {
        return;
    }

    var form = document.getElementById('bulk-form-users-wallet');
    form.querySelectorAll('input[name="ids[]"]').forEach(function(el){ el.remove(); });
    checked.forEach(function(cb) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });
    form.submit();
}
</script>
@endpush
