@extends('admin.layout')

@section('title', 'Marketplace Management - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                Marketplace Management
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Manage categories and features across all marketplace sections
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Categories</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_categories'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <i class="fas fa-tags text-indigo-600 dark:text-indigo-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Active Categories</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active_categories'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Task Categories</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['task_categories'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <i class="fas fa-tasks text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Digital Products</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['digital_product_categories'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <i class="fas fa-download text-orange-600 dark:text-orange-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <a href="{{ route('admin.marketplace.features') }}" class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-4 text-white text-center hover:shadow-lg transition-all">
                <i class="fas fa-toggle-on text-2xl mb-2"></i>
                <p class="font-medium">Feature Toggles</p>
            </a>
            <a href="{{ route('admin.marketplace.create') }}" class="bg-white dark:bg-dark-900 rounded-xl p-4 text-center border border-gray-200 dark:border-dark-700 hover:border-indigo-500 transition-all">
                <i class="fas fa-plus text-indigo-600 text-2xl mb-2"></i>
                <p class="font-medium text-gray-900 dark:text-white">Add Category</p>
            </a>
            <a href="{{ route('admin.analytics') }}" class="bg-white dark:bg-dark-900 rounded-xl p-4 text-center border border-gray-200 dark:border-dark-700 hover:border-indigo-500 transition-all">
                <i class="fas fa-chart-line text-purple-600 text-2xl mb-2"></i>
                <p class="font-medium text-gray-900 dark:text-white">Analytics</p>
            </a>
            <a href="{{ route('admin.settings.notifications') }}" class="bg-white dark:bg-dark-900 rounded-xl p-4 text-center border border-gray-200 dark:border-dark-700 hover:border-indigo-500 transition-all">
                <i class="fas fa-bell text-green-600 text-2xl mb-2"></i>
                <p class="font-medium text-gray-900 dark:text-white">Notifications</p>
            </a>
            <a href="{{ route('admin.settings') }}" class="bg-white dark:bg-dark-900 rounded-xl p-4 text-center border border-gray-200 dark:border-dark-700 hover:border-indigo-500 transition-all">
                <i class="fas fa-cog text-gray-600 text-2xl mb-2"></i>
                <p class="font-medium text-gray-900 dark:text-white">Settings</p>
            </a>
        </div>

        <!-- Categories Table -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg border border-gray-100 dark:border-dark-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-dark-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">All Categories</h2>
                    <form method="GET" class="flex gap-2">
                        <select name="type" class="px-3 py-2 border border-gray-200 dark:border-dark-700 rounded-lg bg-gray-50 dark:bg-dark-800 text-gray-900 dark:text-white text-sm">
                            <option value="">All Types</option>
                            <option value="task" {{ request('type') == 'task' ? 'selected' : '' }}>Tasks</option>
                            <option value="professional" {{ request('type') == 'professional' ? 'selected' : '' }}>Professional Services</option>
                            <option value="growth" {{ request('type') == 'growth' ? 'selected' : '' }}>Growth</option>
                            <option value="digital_product" {{ request('type') == 'digital_product' ? 'selected' : '' }}>Digital Products</option>
                            <option value="job" {{ request('type') == 'job' ? 'selected' : '' }}>Jobs</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Filter</button>
                    </form>
                </div>
            </div>

            <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                <thead class="bg-gray-50 dark:bg-dark-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Parent</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Order</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-800">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($category->icon)
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: {{ $category->color }}20">
                                            <i class="{{ $category->icon }}" style="color: {{ $category->color }}"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $category->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-medium rounded-full 
                                    @switch($category->type)
                                        @case('task') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 @break
                                        @case('professional') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 @break
                                        @case('growth') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 @break
                                        @case('digital_product') bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 @break
                                        @case('job') bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400 @break
                                    @endswitch">
                                    {{ ucfirst(str_replace('_', ' ', $category->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $category->parent->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($category->is_active)
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-dark-700 dark:text-gray-400 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $category->order }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.marketplace.toggle', $category) }}">
                                        @csrf
                                        <button type="submit" class="p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400" title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-toggle-{{ $category->is_active ? 'on' : 'off' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.marketplace.edit', $category) }}" class="p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.marketplace.destroy', $category) }}" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-tags text-4xl mb-4 opacity-50"></i>
                                <p>No categories found. Create your first category!</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-700">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
