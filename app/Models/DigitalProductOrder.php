<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DigitalProductOrder extends Model
{
    protected $fillable = [
        'product_id',
        'buyer_id',
        'order_number',
        'amount',
        'commission',
        'platform_fee',
        'seller_earnings',
        'license_type',
        'license_key',
        'download_token',
        'download_expires_at',
        'download_count',
        'max_downloads',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'seller_earnings' => 'decimal:2',
        'download_expires_at' => 'datetime',
        'download_count' => 'integer',
        'max_downloads' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = 'DPO-' . strtoupper(Str::random(10));
            }
            if (!$order->download_token) {
                $order->download_token = Str::random(64);
            }
            if (!$order->download_expires_at) {
                $order->download_expires_at = now()->addDays(7);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(DigitalProduct::class, 'product_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function getCanDownloadAttribute(): bool
    {
        if (!in_array($this->status, ['pending', 'completed'], true)) {
            return false;
        }
        if ($this->download_count >= $this->max_downloads) {
            return false;
        }
        if ($this->download_expires_at && $this->download_expires_at->isPast()) {
            return false;
        }
        return true;
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
