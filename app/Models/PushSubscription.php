<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'endpoint_hash',
        'p256dh',
        'auth_token',
        'content_encoding',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->endpoint_hash) && !empty($model->endpoint)) {
                $model->endpoint_hash = hash('sha256', $model->endpoint);
            }
        });

        static::updating(function (self $model): void {
            if ($model->isDirty('endpoint')) {
                $model->endpoint_hash = hash('sha256', $model->endpoint);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
