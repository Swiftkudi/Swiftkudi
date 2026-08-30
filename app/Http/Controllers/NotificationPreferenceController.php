<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationPreferenceController extends Controller
{
    public function edit()
    {
        $preference = NotificationPreference::forUser(Auth::user());
        $categories = NotificationPreference::categories();
        $preferences = $preference->resolvedPreferences();

        return view('settings.notifications', compact('preference', 'categories', 'preferences'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'in_app_enabled' => ['nullable', 'boolean'],
            'push_enabled' => ['nullable', 'boolean'],
            'email_enabled' => ['nullable', 'boolean'],
            'email_frequency' => ['required', 'in:instant,daily,weekly'],
            'preferences' => ['nullable', 'array'],
            'preferences.*' => ['nullable', 'array'],
            'preferences.*.*' => ['nullable', 'boolean'],
        ]);

        $preference = NotificationPreference::forUser(Auth::user());
        $resolved = [];
        $posted = $validated['preferences'] ?? [];

        foreach (array_keys(NotificationPreference::categories()) as $category) {
            $resolved[$category] = [
                NotificationPreference::CHANNEL_IN_APP => (bool) ($posted[$category][NotificationPreference::CHANNEL_IN_APP] ?? false),
                NotificationPreference::CHANNEL_PUSH => (bool) ($posted[$category][NotificationPreference::CHANNEL_PUSH] ?? false),
                NotificationPreference::CHANNEL_EMAIL => (bool) ($posted[$category][NotificationPreference::CHANNEL_EMAIL] ?? false),
            ];
        }

        // Security notices must retain in-app/email delivery.
        $resolved['security'][NotificationPreference::CHANNEL_IN_APP] = true;
        $resolved['security'][NotificationPreference::CHANNEL_EMAIL] = true;

        $preference->update([
            'in_app_enabled' => $request->boolean('in_app_enabled'),
            'push_enabled' => $request->boolean('push_enabled'),
            'email_enabled' => $request->boolean('email_enabled'),
            'email_frequency' => $validated['email_frequency'],
            'preferences' => $resolved,
        ]);

        return back()->with('success', 'Notification preferences updated.');
    }
}
