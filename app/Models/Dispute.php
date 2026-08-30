<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    const STATUS_OPEN = 'open';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const RESOLUTION_BUYER_WINS = 'buyer_wins';
    const RESOLUTION_SELLER_WINS = 'seller_wins';
    const RESOLUTION_REFUND = 'refund';
    const RESOLUTION_SPLIT = 'split';

    protected $fillable = [
        'order_id',
        'order_type',
        'disputable_type',
        'disputable_id',
        'raiser_id',
        'responder_id',
        'complainant_id',
        'respondent_id',
        'title',
        'description',
        'status',
        'priority',
        'resolution',
        'resolution_notes',
        'reason',
        'evidence',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'evidence' => 'array',
    ];

    public function disputable()
    {
        return $this->morphTo();
    }

    public function raiser()
    {
        return $this->belongsTo(User::class, 'raiser_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    public function complainant()
    {
        return $this->belongsTo(User::class, 'complainant_id');
    }

    public function respondent()
    {
        return $this->belongsTo(User::class, 'respondent_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function order()
    {
        return $this->morphTo('order');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function isOpen()
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isResolved()
    {
        return $this->status === self::STATUS_RESOLVED;
    }
}
