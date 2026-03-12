@extends('layouts.admin')

@section('title', 'Professional Services Management')

@section('content')
<div class="py-4 lg:py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl lg:text-3xl font-bold text-white">Professional Services Management</h1>
            <p class="text-gray-400 mt-1 text-sm lg:text-base">Review and approve professional service listings</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs lg:text-sm text-gray-400 uppercase">Total Services</p>
                        <p class="text-xl lg:text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-indigo-500/20 flex items-center justify-center">
                        <i class="fas fa-briefcase text-indigo-400 text-lg lg:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs lg:text-sm text-gray-400 uppercase">Pending Review</p>
                        <p class="text-xl lg:text-2xl font-bold text-yellow-400 mt-1">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-400 text-lg lg:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs lg:text-sm text-gray-400 uppercase">Active</p>
                        <p class="text-xl lg:text-2xl font-bold text-green-400 mt-1">{{ $stats['active'] }}</p>
                    </div>
                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-400 text-lg lg:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs lg:text-sm text-gray-400 uppercase">Rejected</p>
                        <p class="text-xl lg:text-2xl font-bold text-red-400 mt-1">{{ $stats['rejected'] }}</p>
                    </div>
                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-red-500/20 flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-400 text-lg lg:text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4 lg:p-6 mb-6">
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="sm:w-48">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-600 rounded-xl text-gray-100 focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="pending" {{ request('status') === 'pending' || !request('status') ? 'selected' : '' }}>Pending</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                    </select>
                </div>
                <div class="sm:self-end flex gap-2">
                    <button type="submit" class="px-4 py-2.5 bg-indigo-500/20 text-indigo-400 rounded-xl hover:bg-indigo-500/30 transition-colors text-sm font-medium">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <a href="{{ route('admin.professional-services') }}" class="px-4 py-2.5 bg-dark-800 text-gray-400 rounded-xl hover:text-white transition-colors text-sm">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Mobile Cards View -->
        <div class="lg:hidden space-y-4">
            <div class="flex items-center justify-between bg-dark-900 border border-dark-700 rounded-xl p-3">
                <p class="text-sm text-gray-300">Select services to delete</p>
                <button type="button" onclick="submitBulkDelete('professional-services')" class="px-3 py-1.5 bg-red-500/20 text-red-400 hover:bg-red-500/30 rounded-lg text-xs font-medium transition-colors">
                    <i class="fas fa-trash mr-1"></i>Delete Selected
                </button>
            </div>
            @if($services->count() > 0)
                @foreach($services as $service)
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.professional-services.show', $service) }}" class="font-medium text-white hover:text-indigo-400 transition-colors">
                                {{ Str::limit($service->title, 40) }}
                            </a>
                            <p class="text-xs text-gray-500 mt-1">ID: {{ $service->id }}</p>
                        </div>
                        @php
                            $statusClasses = [
                                'pending' => 'bg-yellow-500/20 text-yellow-400',
                                'active' => 'bg-green-500/20 text-green-400',
                                'rejected' => 'bg-red-500/20 text-red-400',
                                'paused' => 'bg-gray-500/20 text-gray-400',
                                'draft' => 'bg-dark-700 text-gray-400',
                            ];
                        @endphp
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="ids[]" value="{{ $service->id }}" class="bulk-cb-professional-services w-4 h-4 rounded cursor-pointer">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClasses[$service->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                {{ ucfirst($service->status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-dark-800 rounded-lg p-3">
                            <p class="text-xs text-gray-500 mb-1">Seller</p>
                            @if($service->seller)
                                <a href="{{ route('admin.user-details', $service->seller) }}" class="text-sm text-indigo-400 hover:text-indigo-300">
                                    {{ $service->seller->name }}
                                </a>
                            @else
                                <span class="text-sm text-gray-400">Unknown</span>
                            @endif
                        </div>
                        <div class="bg-dark-800 rounded-lg p-3">
                            <p class="text-xs text-gray-500 mb-1">Price</p>
                            <p class="text-sm font-medium text-green-400">₦{{ number_format($service->price, 2) }}</p>
                        </div>
                        <div class="bg-dark-800 rounded-lg p-3">
                            <p class="text-xs text-gray-500 mb-1">Category</p>
                            <p class="text-sm text-gray-300">{{ $service->category ? $service->category->name : '-' }}</p>
                        </div>
                        <div class="bg-dark-800 rounded-lg p-3">
                            <p class="text-xs text-gray-500 mb-1">Delivery</p>
                            <p class="text-sm text-gray-300">{{ $service->delivery_days }} day(s)</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500">{{ $service->created_at->format('M d, Y') }}</p>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.professional-services.show', $service) }}" class="px-3 py-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors text-sm">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                            @if($service->status === 'pending')
                                <form action="{{ route('admin.professional-services.approve', $service) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-green-500/20 text-green-400 rounded-lg hover:bg-green-500/30 transition-colors text-sm" onclick="return confirm('Approve this service?')">
                                        <i class="fas fa-check mr-1"></i> Approve
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.professional-services.delete', $service) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-colors text-sm" onclick="return confirm('Delete this service? This action cannot be undone.')">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
                
                <div class="mt-4">
                    {{ $services->appends(request()->query())->links() }}
                </div>
            @else
                <div class="bg-dark-900 rounded-2xl shadow-lg border border-dark-700 p-8 text-center">
                    <div class="w-16 h-16 bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-briefcase text-gray-600 text-2xl"></i>
                    </div>
                    <h5 class="text-gray-400 font-medium">No services found</h5>
                    <p class="text-gray-500 text-sm mt-1">There are no services matching the current filter.</p>
                </div>
            @endif
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block bg-dark-900 rounded-2xl shadow-lg border border-dark-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-dark-700">
                <h5 class="font-medium text-white">
                    @if(request('status') === 'active')
                        Active Services
                    @elseif(request('status') === 'rejected')
                        Rejected Services
                    @else
                        Pending Services
                    @endif
                </h5>
            </div>
            
            @if($services->count() > 0)
                <form id="bulk-form-professional-services" action="{{ route('admin.professional-services.bulk-delete') }}" method="POST">@csrf</form>
                <div id="bulk-toolbar-professional-services" class="hidden px-6 py-3 bg-red-500/10 border-b border-red-500/20 flex items-center justify-between">
                    <span class="text-sm text-red-400 font-medium"><span id="bulk-count-professional-services">0</span> selected</span>
                    <button type="button" onclick="submitBulkDelete('professional-services')" class="px-4 py-1.5 bg-red-500/20 text-red-400 hover:bg-red-500/30 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-trash mr-2"></i>Delete Selected
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-dark-700">
                        <thead class="bg-dark-800">
                            <tr>
                                <th class="px-4 py-3 w-10">
                                    <input type="checkbox" id="select-all-professional-services" class="bulk-select-all w-4 h-4 rounded cursor-pointer" data-target="bulk-cb-professional-services">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Seller</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Delivery</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-dark-900 divide-y divide-dark-700">
                            @foreach($services as $service)
                                <tr class="hover:bg-dark-800 transition-colors">
                                    <td class="px-4 py-4">
                                        <input type="checkbox" name="ids[]" value="{{ $service->id }}" class="bulk-cb-professional-services w-4 h-4 rounded cursor-pointer">
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.professional-services.show', $service) }}" class="font-medium text-white hover:text-indigo-400 transition-colors">
                                            {{ Str::limit($service->title, 40) }}
                                        </a>
                                        <p class="text-xs text-gray-500 mt-1">ID: {{ $service->id }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($service->seller)
                                            <a href="{{ route('admin.user-details', $service->seller) }}" class="text-indigo-400 hover:text-indigo-300">
                                                {{ $service->seller->name }}
                                            </a>
                                            <p class="text-xs text-gray-500">{{ $service->seller->email }}</p>
                                        @else
                                            <span class="text-gray-500">Unknown</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($service->category)
                                            <span class="px-2 py-1 bg-dark-800 text-gray-300 rounded-lg text-sm">{{ $service->category->name }}</span>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-medium text-green-400">₦{{ number_format($service->price, 2) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-300">
                                        {{ $service->delivery_days }} day(s)
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-yellow-500/20 text-yellow-400',
                                                'active' => 'bg-green-500/20 text-green-400',
                                                'rejected' => 'bg-red-500/20 text-red-400',
                                                'paused' => 'bg-gray-500/20 text-gray-400',
                                                'draft' => 'bg-dark-700 text-gray-400',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClasses[$service->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                            {{ ucfirst($service->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-300">{{ $service->created_at->format('M d, Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ $service->created_at->diffForHumans() }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.professional-services.show', $service) }}" class="p-2 bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($service->status === 'pending')
                                                <form action="{{ route('admin.professional-services.approve', $service) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="p-2 bg-green-500/20 text-green-400 rounded-lg hover:bg-green-500/30 transition-colors" title="Approve" onclick="return confirm('Approve this service?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.professional-services.delete', $service) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-colors" title="Delete Service" onclick="return confirm('Delete this service? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 border-t border-dark-700">
                    {{ $services->appends(request()->query())->links() }}
                </div>
            @else
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-dark-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-briefcase text-gray-600 text-2xl"></i>
                    </div>
                    <h5 class="text-gray-400 font-medium">No services found</h5>
                    <p class="text-gray-500 text-sm mt-1">There are no services matching the current filter.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
