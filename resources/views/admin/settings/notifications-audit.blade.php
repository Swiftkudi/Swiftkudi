@extends('layouts.admin')

@section('title', 'Notification Audit')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Notification Audit</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Recent in-app notification activity and admin delivery visibility</p>
            </div>
            <div class="mt-4 md:mt-0 space-x-4">
                <a href="{{ route('admin.settings.notifications') }}" class="text-indigo-600 hover:text-indigo-900">← Back to Notification Templates</a>
                <a href="{{ route('admin.settings.notification') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Notification Controls</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Total</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($summary['total']) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Today</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($summary['today']) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Unread</p>
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($summary['unread']) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Admin Recipients</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($summary['admin_total']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 p-4">
            <form method="GET" action="{{ route('admin.settings.notifications-audit') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Search</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Title, message, user" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md">
                        <option value="">All Types</option>
                        @foreach($types as $availableType)
                            <option value="{{ $availableType }}" {{ $type === $availableType ? 'selected' : '' }}>{{ $availableType }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2 pb-2">
                    <input id="admin_only" type="checkbox" name="admin_only" value="1" {{ $adminOnly ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700" />
                    <label for="admin_only" class="text-sm text-gray-700 dark:text-gray-200">Admin recipients only</label>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md">Filter</button>
                    <a href="{{ route('admin.settings.notifications-audit') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-md">Reset</a>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Recipient</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Channel</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($notifications as $notification)
                            @php
                                $recipient = $notification->user;
                                $isAdminRecipient = $recipient && ($recipient->is_admin || !empty($recipient->admin_role_id));
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ optional($notification->created_at)->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium">{{ $recipient->name ?? 'Unknown' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $recipient->email ?? '-' }}</div>
                                    @if($isAdminRecipient)
                                        <span class="inline-flex mt-1 px-2 py-0.5 text-[10px] rounded bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300">Admin</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $notification->type }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-md">
                                    <div class="font-medium">{{ $notification->title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $notification->message }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="inline-flex px-2 py-0.5 text-[10px] rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">In-App</span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($notification->is_read)
                                        <span class="inline-flex px-2 py-0.5 text-[10px] rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">Read</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 text-[10px] rounded bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300">Unread</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No notifications found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
