@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
<div class="py-4 lg:py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Settings Audit Logs</h1>
                <p class="text-gray-400 mt-1 text-sm lg:text-base">Track all changes made to system settings</p>
            </div>
            <a href="{{ route('admin.settings') }}" class="inline-flex items-center text-indigo-400 hover:text-indigo-300 text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Settings
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 mb-6 p-4">
            <form action="{{ route('admin.settings.audit') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-300 mb-2">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Search by setting key or admin..."
                        class="w-full px-4 py-2.5 bg-dark-800 border border-dark-600 rounded-xl text-gray-100 focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div class="sm:w-48">
                    <label for="group" class="block text-sm font-medium text-gray-300 mb-2">Group</label>
                    <select name="group" id="group" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-600 rounded-xl text-gray-100 focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">All Groups</option>
                        @foreach(\App\Models\SystemSetting::GROUPS as $key => $label)
                            <option value="{{ $key }}" {{ request('group') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:self-end">
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-indigo-500/20 text-indigo-400 rounded-xl hover:bg-indigo-500/30 transition-colors text-sm font-medium">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Mobile Cards View -->
        <div class="lg:hidden space-y-4">
            @forelse($logs as $log)
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center">
                            <span class="text-indigo-400 font-medium text-sm">
                                {{ substr($log->admin->name ?? 'U', 0, 2) }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-white text-sm">{{ $log->admin->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-400">{{ $log->admin->email ?? '' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-dark-800 rounded-lg p-3 mb-3">
                    <p class="text-xs text-gray-500 mb-1">Setting Key</p>
                    <code class="text-sm text-indigo-400">{{ $log->setting_key }}</code>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div class="bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Timestamp</p>
                        <p class="text-sm text-gray-300">{{ $log->created_at->format('M d, H:i') }}</p>
                    </div>
                    <div class="bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">IP Address</p>
                        <p class="text-sm text-gray-300">{{ $log->ip_address ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <div class="bg-dark-800 rounded-lg p-3">
                    <p class="text-xs text-gray-500 mb-1">Change</p>
                    <p class="text-sm text-gray-300">
                        <span class="text-green-400">New:</span> {{ $log->masked_new_value }}
                    </p>
                </div>
            </div>
            @empty
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-8 text-center">
                <div class="w-16 h-16 bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-history text-gray-600 text-2xl"></i>
                </div>
                <p class="text-gray-400">No audit logs found</p>
            </div>
            @endforelse
            
            <!-- Mobile Pagination -->
            <div class="mt-4">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block bg-dark-900 rounded-2xl shadow-lg border border-dark-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-dark-700">
                    <thead class="bg-dark-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Timestamp</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Admin</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Setting Key</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Change</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="bg-dark-900 divide-y divide-dark-700">
                        @forelse($logs as $log)
                        <tr class="hover:bg-dark-800 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center">
                                        <span class="text-indigo-400 font-medium text-sm">
                                            {{ substr($log->admin->name ?? 'U', 0, 2) }}
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-white">{{ $log->admin->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-400">{{ $log->admin->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <code class="bg-dark-800 px-2 py-1 rounded text-sm text-indigo-400">{{ $log->setting_key }}</code>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-300">
                                <div class="max-w-xs truncate">
                                    <span class="text-green-400">New:</span> {{ $log->masked_new_value }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ $log->ip_address ?? 'N/A' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-history text-gray-600 text-3xl mb-3"></i>
                                    <p class="text-gray-400">No audit logs found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Desktop Pagination -->
        <div class="hidden lg:block mt-4">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
