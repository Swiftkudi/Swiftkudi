<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'attachment_type',
        'attachment_path',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(MarketplaceConversation::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
