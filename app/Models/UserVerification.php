<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    use HasFactory;

    const TYPE_EMAIL = 'email';
    const TYPE_PHONE = 'phone';
    const TYPE_IDENTITY = 'identity';
    const TYPE_ADDRESS = 'address';

    const STATUS_PENDING = 'pending';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'verified_at',
        'document_type',
        'document_number',
        'document_front',
        'document_back',
        'address_proof',
        'phone',
        'phone_verified_at',
        'verification_data',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'rejected_at' => 'datetime',
        'verification_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeEmail($query)
    {
        return $query->where('type', self::TYPE_EMAIL);
    }

    public function scopePhone($query)
    {
        return $query->where('type', self::TYPE_PHONE);
    }

    public function scopeIdentity($query)
    {
        return $query->where('type', self::TYPE_IDENTITY);
    }

    public function scopeAddress($query)
    {
        return $query->where('type', self::TYPE_ADDRESS);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isVerified()
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function markAsVerified()
    {
        $this->status = self::STATUS_VERIFIED;
        $this->verified_at = now();
        $this->save();
    }

    public function markAsRejected($reason = null)
    {
        $this->status = self::STATUS_REJECTED;
        $this->rejected_at = now();
        $this->rejection_reason = $reason;
        $this->save();
    }
}
