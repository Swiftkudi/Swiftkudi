@extends('layouts.admin')

@section('title', 'Email Delivery Diagnostics')

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div><h1 class="text-2xl font-bold text-gray-100">Email Delivery Diagnostics</h1><p class="mt-1 text-sm text-gray-400">Track transactional notification attempts. Provider-side delivery, bounce and complaint events require webhook support from your SMTP provider.</p></div>
            <div class="flex flex-wrap gap-3 text-sm"><a class="text-indigo-400 hover:text-indigo-300" href="{{ route('admin.settings.notifications-audit') }}">In-app audit</a><a class="text-gray-300 hover:text-white" href="{{ route('admin.settings.smtp') }}">SMTP settings</a></div>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([['Total',$summary['total'],'text-gray-100'],['Sent',$summary['sent'],'text-green-400'],['Retrying',$summary['retrying'],'text-yellow-400'],['Failed',$summary['failed'],'text-red-400']] as $card)
            <div class="rounded-xl border border-gray-700 bg-gray-800 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $card[0] }}</p><p class="mt-2 text-2xl font-bold {{ $card[2] }}">{{ number_format($card[1]) }}</p></div>
            @endforeach
        </div>

        <form method="GET" class="mb-6 grid gap-3 rounded-xl border border-gray-700 bg-gray-800 p-4 md:grid-cols-[1fr_220px_auto]">
            <input name="search" value="{{ $search }}" placeholder="Recipient, subject or correlation ID" class="rounded-lg border-gray-600 bg-gray-900 text-gray-100">
            <select name="status" class="rounded-lg border-gray-600 bg-gray-900 text-gray-100"><option value="">All statuses</option>@foreach($statuses as $availableStatus)<option value="{{ $availableStatus }}" {{ $status === $availableStatus ? 'selected' : '' }}>{{ ucfirst($availableStatus) }}</option>@endforeach</select>
            <div class="flex gap-2"><button class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-500">Filter</button><a href="{{ route('admin.settings.email-deliveries') }}" class="rounded-lg border border-gray-600 px-4 py-2 text-gray-300">Reset</a></div>
        </form>

        <div class="overflow-hidden rounded-xl border border-gray-700 bg-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700 text-sm">
                    <thead class="bg-gray-900/50 text-left text-xs uppercase tracking-wide text-gray-500"><tr><th class="px-4 py-3">Time</th><th class="px-4 py-3">Recipient</th><th class="px-4 py-3">Subject</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Attempts</th><th class="px-4 py-3">Diagnostic</th></tr></thead>
                    <tbody class="divide-y divide-gray-700">
                    @forelse($deliveries as $delivery)
                        <tr class="align-top"><td class="whitespace-nowrap px-4 py-3 text-gray-400">{{ optional($delivery->created_at)->format('Y-m-d H:i:s') }}</td><td class="px-4 py-3"><p class="text-gray-200">{{ optional($delivery->user)->name ?: 'User' }}</p><p class="text-xs text-gray-500">{{ $delivery->recipient_email }}</p></td><td class="max-w-xs px-4 py-3 text-gray-300">{{ $delivery->subject }}</td><td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold {{ $delivery->status === 'sent' ? 'bg-green-500/10 text-green-400' : ($delivery->status === 'failed' ? 'bg-red-500/10 text-red-400' : 'bg-yellow-500/10 text-yellow-400') }}">{{ ucfirst($delivery->status) }}</span></td><td class="px-4 py-3 text-gray-300">{{ $delivery->attempts }}</td><td class="max-w-sm px-4 py-3 text-xs text-gray-500">{{ $delivery->last_error ?: ($delivery->sent_at ? 'Accepted by configured mail transport at '.$delivery->sent_at->format('Y-m-d H:i:s') : 'No transport error recorded.') }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">No email delivery attempts recorded yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-700 px-4 py-3">{{ $deliveries->links() }}</div>
        </div>
    </div>
</div>
@endsection
