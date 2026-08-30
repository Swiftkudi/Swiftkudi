@extends('layouts.app')

@section('title', 'Notification Settings | SwiftKudi')
@section('meta_description', 'Choose how SwiftKudi sends important marketplace, message, payment, and account notifications.')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="marketplace-page">
    <div class="marketplace-container max-w-5xl">
        <div class="marketplace-page-header">
            <div>
                <p class="marketplace-eyebrow">Account preferences</p>
                <h1 class="marketplace-title">Notification settings</h1>
                <p class="marketplace-subtitle">Choose which updates reach you in SwiftKudi, by browser push, and by email.</p>
            </div>
            <a href="{{ route('notifications.index') }}" class="marketplace-btn marketplace-btn-secondary">View notifications</a>
        </div>

        <form method="POST" action="{{ route('notification-settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="marketplace-card p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Channels</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Turn an entire delivery channel on or off.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    @foreach([
                        'in_app_enabled' => ['In-app', 'Bell and notification centre'],
                        'push_enabled' => ['Push', 'Browser/device notifications'],
                        'email_enabled' => ['Email', 'Transactional email updates'],
                    ] as $field => [$label, $description])
                        <label class="marketplace-option-card">
                            <span>
                                <span class="block font-semibold text-slate-900 dark:text-white">{{ $label }}</span>
                                <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $description }}</span>
                            </span>
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1" class="marketplace-checkbox" {{ $preference->{$field} ? 'checked' : '' }}>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="marketplace-card p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Browser push</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Permission is requested only when you choose to enable it. SwiftKudi will not interrupt you with an automatic browser prompt.</p>
                        <p id="push-status" class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-300">Checking this browser…</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="push-enable" class="marketplace-btn marketplace-btn-primary">Enable push</button>
                        <button type="button" id="push-test" class="marketplace-btn marketplace-btn-secondary">Send test</button>
                        <button type="button" id="push-disable" class="marketplace-btn marketplace-btn-secondary">Disable on this browser</button>
                    </div>
                </div>
            </section>

            <section class="marketplace-card overflow-hidden">
                <div class="border-b border-slate-200 dark:border-slate-800 p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Marketplace notifications</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Fine-tune each category. Security notices always stay enabled in-app and by email.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Category</th>
                                <th class="px-5 py-3 text-center font-semibold">In-app</th>
                                <th class="px-5 py-3 text-center font-semibold">Push</th>
                                <th class="px-5 py-3 text-center font-semibold">Email</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($categories as $key => $label)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-slate-900 dark:text-white">{{ $label }}</div>
                                        @if($key === 'security')
                                            <div class="mt-1 text-xs text-slate-500">Critical account protection notices cannot be silenced.</div>
                                        @endif
                                    </td>
                                    @foreach(['in_app', 'push', 'email'] as $channel)
                                        @php $locked = $key === 'security' && in_array($channel, ['in_app', 'email']); @endphp
                                        <td class="px-5 py-4 text-center">
                                            <input type="hidden" name="preferences[{{ $key }}][{{ $channel }}]" value="0">
                                            <input type="checkbox"
                                                   name="preferences[{{ $key }}][{{ $channel }}]"
                                                   value="1"
                                                   class="marketplace-checkbox"
                                                   {{ ($preferences[$key][$channel] ?? false) ? 'checked' : '' }}
                                                   {{ $locked ? 'disabled' : '' }}>
                                            @if($locked)
                                                <input type="hidden" name="preferences[{{ $key }}][{{ $channel }}]" value="1">
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="marketplace-card p-5 sm:p-6">
                <label for="email_frequency" class="block text-sm font-semibold text-slate-900 dark:text-white">Email frequency</label>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Instant is recommended for contracts, messages, and payment activity.</p>
                <select id="email_frequency" name="email_frequency" class="marketplace-input mt-4 max-w-sm">
                    <option value="instant" {{ $preference->email_frequency === 'instant' ? 'selected' : '' }}>Instant</option>
                    <option value="daily" {{ $preference->email_frequency === 'daily' ? 'selected' : '' }}>Daily digest (non-critical)</option>
                    <option value="weekly" {{ $preference->email_frequency === 'weekly' ? 'selected' : '' }}>Weekly digest (non-critical)</option>
                </select>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="marketplace-btn marketplace-btn-primary">Save preferences</button>
            </div>
        </form>
    </div>
</div>
@endsection


@push('scripts')
<script>
(function () {
    const vapidKey = @json(config('services.vapid.public_key'));
    const statusEl = document.getElementById('push-status');
    const enableBtn = document.getElementById('push-enable');
    const disableBtn = document.getElementById('push-disable');
    const testBtn = document.getElementById('push-test');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function base64ToBytes(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw = atob(base64);
        return Uint8Array.from([...raw].map(char => char.charCodeAt(0)));
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(payload || {}),
        });
        const body = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(body.message || 'Request failed.');
        return body;
    }

    async function registration() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
            throw new Error('Push notifications are not supported by this browser.');
        }
        if (!vapidKey) throw new Error('Push notifications are not configured on this server yet.');
        await navigator.serviceWorker.register('/sw.js', { scope: '/' });
        return navigator.serviceWorker.ready;
    }

    async function syncStatus() {
        try {
            const reg = await registration();
            const sub = await reg.pushManager.getSubscription();
            if (Notification.permission === 'denied') {
                statusEl.textContent = 'Blocked in this browser. Allow notifications in your browser site settings to enable push.';
            } else if (sub && Notification.permission === 'granted') {
                statusEl.textContent = 'Enabled on this browser.';
            } else {
                statusEl.textContent = 'Not enabled on this browser.';
            }
            testBtn.disabled = !sub || Notification.permission !== 'granted';
            disableBtn.disabled = !sub;
        } catch (error) {
            statusEl.textContent = error.message;
            enableBtn.disabled = true;
            testBtn.disabled = true;
            disableBtn.disabled = true;
        }
    }

    enableBtn?.addEventListener('click', async () => {
        enableBtn.disabled = true;
        try {
            const reg = await registration();
            const permission = Notification.permission === 'granted' ? 'granted' : await Notification.requestPermission();
            if (permission !== 'granted') throw new Error('Notification permission was not granted.');
            let sub = await reg.pushManager.getSubscription();
            if (!sub) {
                sub = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: base64ToBytes(vapidKey),
                });
            }
            const data = sub.toJSON();
            await postJson(@json(route('push.subscribe')), {
                endpoint: data.endpoint,
                keys: data.keys,
                contentEncoding: (PushManager.supportedContentEncodings || ['aes128gcm'])[0],
            });
            const preference = document.getElementById('push_enabled');
            if (preference) preference.checked = true;
            statusEl.textContent = 'Enabled on this browser. Save preferences to keep the Push channel enabled.';
        } catch (error) {
            statusEl.textContent = error.message;
        } finally {
            enableBtn.disabled = false;
            syncStatus();
        }
    });

    disableBtn?.addEventListener('click', async () => {
        try {
            const reg = await registration();
            const sub = await reg.pushManager.getSubscription();
            if (sub) {
                await postJson(@json(route('push.unsubscribe')), { endpoint: sub.endpoint });
                await sub.unsubscribe();
            }
            statusEl.textContent = 'Disabled on this browser.';
            await syncStatus();
        } catch (error) {
            statusEl.textContent = error.message;
        }
    });

    testBtn?.addEventListener('click', async () => {
        testBtn.disabled = true;
        try {
            const result = await postJson(@json(route('push.test')), {});
            statusEl.textContent = result.message || 'Test push sent.';
        } catch (error) {
            statusEl.textContent = error.message;
        } finally {
            testBtn.disabled = false;
        }
    });

    syncStatus();
}());
</script>
@endpush
