<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdempotencyKey extends Model
{
    protected $table = 'idempotency_keys';

    protected $fillable = [
        'key',
        'user_id',
        'entity_type',
        'entity_id',
        'method',
        'request_hash',
        'response_status',
        'response_body',
        'expires_at',
    ];

    protected $casts = [
        'request_hash' => 'array',
        'response_body' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getOrCreate(string $key, ?int $userId = null, array $requestData = []): self
    {
        $instance = self::where('key', $key)->first();

        if (!$instance) {
            $instance = self::create([
                'key' => $key,
                'user_id' => $userId,
                'request_hash' => $requestData,
                'expires_at' => now()->addHours(24),
            ]);
        }

        return $instance;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function markAsProcessed(string $status, array $response = []): void
    {
        $this->update([
            'response_status' => $status,
            'response_body' => $response,
        ]);
    }

    public function getResponse(): ?array
    {
        if ($this->response_status && $this->response_body) {
            return [
                'status' => $this->response_status,
                'body' => $this->response_body,
            ];
        }
        return null;
    }

    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', now())->delete();
    }
}