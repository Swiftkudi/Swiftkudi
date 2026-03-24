<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceConversation extends Model
{
    protected $fillable = [
        'type',
        'reference_id',
        'buyer_id',
        'seller_id',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages()
    {
        return $this->hasMany(MarketplaceMessage::class, 'conversation_id')->orderBy('created_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(MarketplaceMessage::class, 'conversation_id')->latestOfMany();
    }

    public function unreadMessages()
    {
        return $this->hasMany(MarketplaceMessage::class, 'conversation_id')->where('is_read', false);
    }

    public function unreadCount()
    {
        return $this->unreadMessages()->count();
    }

    public function reference()
    {
        return $this->morphTo('reference', 'type', 'reference_id');
    }

    public function markAsRead()
    {
        $this->unreadMessages()->update(['is_read' => true]);
    }

    public function close()
    {
        $this->update(['status' => 'closed']);
    }

    public function resolve()
    {
        $this->update(['status' => 'resolved']);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('buyer_id', $userId)->orWhere('seller_id', $userId);
        });
    }

    public static function findOrCreate($type, $referenceId, $buyerId, $sellerId)
    {
        $conversation = self::where('type', $type)
            ->where('reference_id', $referenceId)
            ->where(function ($q) use ($buyerId, $sellerId) {
                $q->where(function ($q2) use ($buyerId, $sellerId) {
                    $q2->where('buyer_id', $buyerId)->where('seller_id', $sellerId);
                })->orWhere(function ($q2) use ($buyerId, $sellerId) {
                    $q2->where('buyer_id', $sellerId)->where('seller_id', $buyerId);
                });
            })
            ->first();

        if (!$conversation) {
            $conversation = self::create([
                'type' => $type,
                'reference_id' => $referenceId,
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'status' => 'active',
            ]);
        }

        return $conversation;
    }
}
