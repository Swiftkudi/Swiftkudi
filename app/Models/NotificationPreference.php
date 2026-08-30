<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    public const CHANNEL_IN_APP = 'in_app';
    public const CHANNEL_PUSH = 'push';
    public const CHANNEL_EMAIL = 'email';

    protected $fillable = [
        'user_id',
        'in_app_enabled',
        'push_enabled',
        'email_enabled',
        'preferences',
        'email_frequency',
    ];

    protected $casts = [
        'in_app_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'preferences' => 'array',
    ];

    public static function categories(): array
    {
        return [
            'messages' => 'Messages',
            'proposals' => 'Proposals & applications',
            'jobs' => 'Jobs & opportunities',
            'contracts' => 'Contracts & milestones',
            'payments' => 'Payments & wallet',
            'reviews' => 'Reviews & reputation',
            'disputes' => 'Disputes & resolutions',
            'security' => 'Security & account',
            'marketing' => 'Recommendations & marketing',
            'system' => 'Platform & system updates',
        ];
    }

    public static function defaultPreferences(): array
    {
        $defaults = [];
        foreach (array_keys(static::categories()) as $category) {
            $defaults[$category] = [
                self::CHANNEL_IN_APP => true,
                self::CHANNEL_PUSH => in_array($category, ['messages', 'proposals', 'contracts', 'payments', 'security'], true),
                self::CHANNEL_EMAIL => $category !== 'marketing',
            ];
        }

        // Security notices remain enabled on durable channels for account safety.
        $defaults['security'][self::CHANNEL_IN_APP] = true;
        $defaults['security'][self::CHANNEL_EMAIL] = true;

        return $defaults;
    }

    public static function forUser(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id],
            [
                'in_app_enabled' => true,
                'push_enabled' => true,
                'email_enabled' => true,
                'preferences' => static::defaultPreferences(),
                'email_frequency' => 'instant',
            ]
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedPreferences(): array
    {
        $stored = is_array($this->preferences) ? $this->preferences : [];
        return array_replace_recursive(static::defaultPreferences(), $stored);
    }

    public function channelEnabled(string $category, string $channel): bool
    {
        $global = match ($channel) {
            self::CHANNEL_IN_APP => $this->in_app_enabled,
            self::CHANNEL_PUSH => $this->push_enabled,
            self::CHANNEL_EMAIL => $this->email_enabled,
            default => false,
        };

        if (!$global) {
            return false;
        }

        // Security notices cannot be completely silenced in-app/email.
        if ($category === 'security' && in_array($channel, [self::CHANNEL_IN_APP, self::CHANNEL_EMAIL], true)) {
            return true;
        }

        $preferences = $this->resolvedPreferences();
        return (bool) ($preferences[$category][$channel] ?? false);
    }
}
