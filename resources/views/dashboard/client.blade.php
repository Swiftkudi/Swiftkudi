@extends('layouts.app')

@section('title', 'Client Dashboard - SwiftKudi')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Client Dashboard</h1>
                <p class="text-gray-600 mt-1">Manage your campaigns and track performance</p>
            </div>
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-2"></i> Create Campaign
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <!-- Total Campaigns -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-bullhorn text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Campaigns</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_campaigns'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Campaigns -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Active Campaigns</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['active_campaigns'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Submissions -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clipboard-check text-indigo-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Submissions</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_submissions'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Spent -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-naira-sign text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Spent</p>
                        <p class="text-2xl font-bold text-gray-900">₦{{ number_format($stats['total_spent'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals Alert -->
        @if($pendingApprovals > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                        <div>
                            <p class="font-medium text-yellow-800">Pending Approvals</p>
                            <p class="text-sm text-yellow-600">You have {{ $pendingApprovals }} submission(s) waiting for review</p>
                        </div>
                    </div>
                    <a href="{{ route('tasks.my-tasks') }}" class="px-4 py-2 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition">
                        Review Now
                    </a>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- My Campaigns -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-900">My Campaigns</h2>
                        <a href="{{ route('tasks.create') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                            <i class="fas fa-plus mr-1"></i> New Campaign
                        </a>
                    </div>

                    @if($campaigns->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaign</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($campaigns as $campaign)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                                        <i class="fas fa-{{ $campaign->category->icon ?? 'tasks' }} text-gray-500"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $campaign->title }}</p>
                                                        <p class="text-xs text-gray-500">{{ $campaign->category->name ?? 'General' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($campaign->status === 'active')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @elseif($campaign->status === 'pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                @elseif($campaign->status === 'completed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Completed
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ ucfirst($campaign->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, ($campaign->completed_count / $campaign->quantity) * 100) }}%"></div>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">{{ $campaign->completed_count }}/{{ $campaign->quantity }} completed</p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ₦{{ number_format($campaign->budget, 0) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('tasks.show', $campaign) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($campaign->status === 'active')
                                                    <a href="{{ route('tasks.analytics', $campaign) }}" class="text-green-600 hover:text-green-900 mr-3">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $campaigns->links() }}
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <i class="fas fa-bullhorn text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">No campaigns yet</p>
                            <p class="text-sm text-gray-400 mt-2 mb-4">Create your first campaign to start promoting</p>
                            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                                <i class="fas fa-plus mr-2"></i> Create Campaign
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="{{ route('tasks.create') }}" class="flex items-center p-3 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                            <i class="fas fa-plus text-indigo-600 mr-3"></i>
                            <span class="text-indigo-700 font-medium">New Campaign</span>
                        </a>
                        <a href="{{ route('tasks.my-tasks') }}" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition">
                            <i class="fas fa-check-double text-green-600 mr-3"></i>
                            <span class="text-green-700 font-medium">Review Submissions</span>
                        </a>
                        <a href="{{ route('wallet.deposit') }}" class="flex items-center p-3 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                            <i class="fas fa-plus-circle text-yellow-600 mr-3"></i>
                            <span class="text-yellow-700 font-medium">Add Funds</span>
                        </a>
                    </div>
                </div>

                <!-- Campaign Tips -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Tips for Success</h2>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-1"></i>
                            <span>Use clear instructions for better task completion rates</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-1"></i>
                            <span>Feature your campaigns to reach more workers</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-1"></i>
                            <span>Review submissions within 24 hours to keep workers engaged</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-1"></i>
                            <span>Offer competitive rewards to attract quality workers</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
