<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referred_user_id',
        'referral_code',
        'referred_email',
        'is_registered',
        'is_activated',
        'reward_earned',
    ];

    protected $casts = [
        'is_registered' => 'boolean',
        'is_activated' => 'boolean',
        'reward_earned' => 'decimal:2',
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'reward_earned' => 0,
    ];

    /**
     * Relationship: user who referred
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: the referred user (if registered)
     */
    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    /**
     * Alias for referred user relationship used in views
     */
    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    /**
     * Check if referral is activated
     */
    public function isActivated(): bool
    {
        return (bool) $this->is_activated;
    }

    /**
     * Check if referred user is registered
     */
    public function isRegistered(): bool
    {
        return (bool) $this->is_registered;
    }

    /**
     * Mark as registered
     */
    public function markAsRegistered(): bool
    {
        $this->is_registered = true;
        $this->save();

        return true;
    }

    /**
     * Mark as activated with reward details
     */
    public function markAsActivated(float $reward): bool
    {
        $this->is_activated = true;
        $this->reward_earned = $reward;
        $this->save();

        return true;
    }

    /**
     * Scope: Activated referrals
     */
    public function scopeActivated($query)
    {
        return $query->where('is_activated', true);
    }

    /**
     * Scope: Pending referrals
     */
    public function scopePending($query)
    {
        return $query->where('is_activated', false);
    }

    /**
     * Scope: By user (referrer)
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get formatted reward
     */
    public function getFormattedRewardAttribute(): string
    {
        return '₦' . number_format($this->reward_earned, 2);
    }

    /**
     * Generate a unique referral code
     *
     * Accepts either a user id (will use the user's name/email) or a preferred
     * string (for example a username). Produces a human-friendly code when
     * possible, falling back to a random code when needed.
     *
     * @param int|string|null $userIdOrPreferred
     * @return string
     */
    public static function generateReferralCode($userIdOrPreferred = null): string
    {
        $preferred = null;

        // If an integer-like value is provided, try to resolve a user and use their name or email local part
        if (is_int($userIdOrPreferred) || (is_string($userIdOrPreferred) && ctype_digit($userIdOrPreferred))) {
            $user = \App\Models\User::find(intval($userIdOrPreferred));
            if ($user) {
                // prefer name, otherwise use email local part before @
                if (!empty($user->name)) {
                    $preferred = $user->name;
                } elseif (!empty($user->email)) {
                    $parts = explode('@', $user->email);
                    $preferred = $parts[0] ?? $user->email;
                }
            }
        }

        // If a non-numeric string provided, treat it as the preferred base
        if (!$preferred && is_string($userIdOrPreferred) && !ctype_digit($userIdOrPreferred)) {
            $preferred = $userIdOrPreferred;
        }

        if ($preferred) {
            // sanitize to alphanumeric uppercase and limit length
            $base = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($preferred));
            $base = substr($base, 0, 8);

            // Ensure the code doesn't become numeric-only by prefixing when necessary
            if (ctype_digit($base) || $base === '') {
                $base = 'ED' . $base; // prefix to avoid numeric-only codes
                $base = substr($base, 0, 8);
            }

            if ($base) {
                do {
                    $suffix = strtoupper(substr(md5(uniqid((string) rand(), true)), 0, 4));
                    $code = $base . $suffix;
                } while (self::where('referral_code', $code)->exists());

                return $code;
            }
        }

        // Fallback to original random code
        do {
            $code = strtoupper(substr(md5(uniqid((string) rand(), true)), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Status accessor computed from flags
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_activated) {
            return 'activated';
        }

        if ($this->is_registered) {
            return 'registered';
        }

        return 'pending';
    }
}
