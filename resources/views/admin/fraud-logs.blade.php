@extends('layouts.admin')

@section('title', 'Fraud Logs')

@section('content')
<div class="py-4 lg:py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Fraud Logs</h1>
                <p class="text-gray-400 mt-1 text-sm lg:text-base">Suspicious activity detected on the platform</p>
            </div>
            <a href="{{ route('admin.index') }}" class="inline-flex items-center text-indigo-400 hover:text-indigo-300 text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Admin
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-xl">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <!-- Mobile Cards View -->
        <div class="lg:hidden space-y-4">
            @forelse($logs as $log)
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full @if($log->severity === 'high') bg-red-500/20 @elseif($log->severity === 'medium') bg-yellow-500/20 @else bg-blue-500/20 @endif flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle @if($log->severity === 'high') text-red-400 @elseif($log->severity === 'medium') text-yellow-400 @else text-blue-400 @endif"></i>
                        </div>
                        <div>
                            <p class="font-medium text-white text-sm">#{{ $log->id }}</p>
                            <p class="text-sm text-gray-400">{{ optional($log->user)->name ?? 'System' }}</p>
                        </div>
                    </div>
                    @if($log->is_resolved)
                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400">
                        Resolved
                    </span>
                    @else
                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400">
                        Open
                    </span>
                    @endif
                </div>
                
                <div class="bg-dark-800 rounded-lg p-3 mb-3">
                    <p class="text-sm text-gray-300">{{ Str::limit($log->description ?? $log->message ?? '—', 150) }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div class="bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Severity</p>
                        <span class="text-sm @if($log->severity === 'high') text-red-400 @elseif($log->severity === 'medium') text-yellow-400 @else text-blue-400 @endif">
                            {{ ucfirst($log->severity ?? 'info') }}
                        </span>
                    </div>
                    <div class="bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">When</p>
                        <p class="text-sm text-gray-300">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                
                @if(!$log->is_resolved)
                <form method="POST" action="{{ route('admin.fraud-logs.resolve', $log) }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-green-500/20 text-green-400 hover:bg-green-500/30 transition-colors text-sm font-medium">
                        <i class="fas fa-check mr-2"></i>Mark Resolved
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route('admin.fraud-logs.delete', $log) }}" class="mt-2" onsubmit="return confirm('Delete this fraud log permanently?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-gray-500/20 text-gray-400 hover:bg-red-500/20 hover:text-red-400 transition-colors text-sm font-medium">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </form>
            </div>
            @empty
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-8 text-center">
                <div class="w-16 h-16 bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-gray-600 text-2xl"></i>
                </div>
                <p class="text-gray-400">No fraud logs found.</p>
            </div>
            @endforelse
            
            <!-- Mobile Pagination -->
            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block bg-dark-900 rounded-2xl shadow-lg border border-dark-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-dark-700">
                    <thead class="bg-dark-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Severity</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">When</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-dark-900 divide-y divide-dark-700">
                        @forelse($logs as $log)
                        <tr class="hover:bg-dark-800 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-300">{{ $log->id }}</td>
                            <td class="px-6 py-4 text-sm text-white">{{ optional($log->user)->name ?? 'System' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-300">{{ Str::limit($log->description ?? $log->message ?? '—', 80) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold @if($log->severity === 'high') bg-red-500/20 text-red-400 @elseif($log->severity === 'medium') bg-yellow-500/20 text-yellow-400 @else bg-blue-500/20 text-blue-400 @endif">
                                    {{ ucfirst($log->severity ?? 'info') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">{{ $log->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4">
                                @if($log->is_resolved)
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400">Resolved</span>
                                @else
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400">Open</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    @if(!$log->is_resolved)
                                    <form method="POST" action="{{ route('admin.fraud-logs.resolve', $log) }}">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 rounded-lg bg-green-500/20 text-green-400 hover:bg-green-500/30 transition-colors text-sm">
                                            <i class="fas fa-check mr-1"></i>Resolve
                                        </button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.fraud-logs.delete', $log) }}" onsubmit="return confirm('Delete this fraud log permanently?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-2 rounded-lg bg-gray-500/20 text-gray-400 hover:bg-red-500/20 hover:text-red-400 transition-colors text-sm" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-sm text-gray-400 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-shield-alt text-gray-600 text-2xl mb-2"></i>
                                    No fraud logs found.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-dark-700">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
