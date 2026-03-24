<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscrowTransaction extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_FUNDED = 'funded';
    const STATUS_RELEASED = 'released';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_DISPUTED = 'disputed';

    protected $fillable = [
        'transaction_no',
        'order_id',
        'order_type',
        'payer_id',
        'payee_id',
        'amount',
        'platform_fee',
        'total_amount',
        'status',
        'released_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'released_at' => 'datetime',
    ];

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee()
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    public function order()
    {
        return $this->morphTo('order');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFunded($query)
    {
        return $query->where('status', self::STATUS_FUNDED);
    }

    public function scopeReleased($query)
    {
        return $query->where('status', self::STATUS_RELEASED);
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', self::STATUS_DISPUTED);
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFunded()
    {
        return $this->status === self::STATUS_FUNDED;
    }

    public function isReleased()
    {
        return $this->status === self::STATUS_RELEASED;
    }

    public function isDisputed()
    {
        return $this->status === self::STATUS_DISPUTED;
    }

    public function release()
    {
        $this->status = self::STATUS_RELEASED;
        $this->released_at = now();
        $this->save();
    }

    public function cancel()
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();
    }

    public function markAsDisputed()
    {
        $this->status = self::STATUS_DISPUTED;
        $this->save();
    }
}
