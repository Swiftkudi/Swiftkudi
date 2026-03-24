<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingsAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'setting_key',
        'old_value',
        'new_value',
        'group',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_value' => 'string',
        'new_value' => 'string',
    ];

    /**
     * Relationship: Admin user
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope: By admin
     */
    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope: By setting key
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('setting_key', 'like', "%{$key}%");
    }

    /**
     * Scope: By group
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope: Recent
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get masked value for display (hide sensitive data)
     */
    public function getMaskedNewValueAttribute(): string
    {
        $value = $this->new_value ?? '';

        // Mask potential sensitive values
        $sensitivePatterns = [
            '/sk_live_[a-zA-Z0-9]+/' => 'sk_live_****',
            '/pk_live_[a-zA-Z0-9]+/' => 'pk_live_****',
            '/password/i' => '********',
            '/secret/i' => '********',
        ];

        foreach ($sensitivePatterns as $pattern => $replacement) {
            $value = preg_replace($pattern, $replacement, $value);
        }

        return $value;
    }

    /**
     * Get human-readable action
     */
    public function getActionAttribute(): string
    {
        return 'Updated';
    }
}
