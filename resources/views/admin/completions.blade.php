@extends('layouts.admin')

@section('title', 'Task Completions')

@section('content')
<div class="py-4 lg:py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">Task Completions</h1>
                <p class="text-gray-400 mt-1 text-sm lg:text-base">Pending task completions awaiting review</p>
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
            @forelse($completions as $completion)
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                            #{{ $completion->id }}
                        </div>
                        <div>
                            <p class="font-medium text-white">{{ optional($completion->task)->title ?? '—' }}</p>
                            <p class="text-sm text-gray-400">{{ optional($completion->user)->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Submitted</p>
                        <p class="text-sm text-gray-300">{{ $completion->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Status</p>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400">
                            Pending
                        </span>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <form method="POST" action="{{ url('/admin/completions/'.$completion->id.'/approve') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-green-500/20 text-green-400 hover:bg-green-500/30 transition-colors text-sm font-medium">
                            <i class="fas fa-check mr-2"></i>Approve
                        </button>
                    </form>
                    <form method="POST" action="{{ url('/admin/completions/'.$completion->id.'/reject') }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="notes" value="Rejected by admin">
                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors text-sm font-medium">
                            <i class="fas fa-times mr-2"></i>Reject
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.completions.delete', $completion) }}" onsubmit="return confirm('Delete this completion record?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-gray-500/20 text-gray-400 hover:bg-red-500/20 hover:text-red-400 transition-colors text-sm font-medium">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-8 text-center">
                <div class="w-16 h-16 bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-gray-600 text-2xl"></i>
                </div>
                <p class="text-gray-400">No pending completions found.</p>
            </div>
            @endforelse
            
            <!-- Mobile Pagination -->
            <div class="mt-4">
                {{ $completions->links() }}
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block bg-dark-900 rounded-2xl shadow-lg border border-dark-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-dark-700">
                <h3 class="text-lg font-medium text-white">Pending Completions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-dark-700">
                    <thead class="bg-dark-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Task</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-dark-900 divide-y divide-dark-700">
                        @forelse($completions as $completion)
                        <tr class="hover:bg-dark-800 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-300">{{ $completion->id }}</td>
                            <td class="px-6 py-4 text-sm text-white">{{ optional($completion->task)->title ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-white">{{ optional($completion->user)->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-400">{{ $completion->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ url('/admin/completions/'.$completion->id.'/approve') }}">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 rounded-lg bg-green-500/20 text-green-400 hover:bg-green-500/30 transition-colors text-sm">
                                            <i class="fas fa-check mr-1"></i>Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ url('/admin/completions/'.$completion->id.'/reject') }}">
                                        @csrf
                                        <input type="hidden" name="notes" value="Rejected by admin">
                                        <button type="submit" class="px-4 py-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors text-sm">
                                            <i class="fas fa-times mr-1"></i>Reject
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.completions.delete', $completion) }}" onsubmit="return confirm('Delete this completion record?')">
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
                            <td colspan="5" class="px-6 py-8 text-sm text-gray-400 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-check-circle text-gray-600 text-2xl mb-2"></i>
                                    No pending completions found.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-dark-700">
                {{ $completions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
