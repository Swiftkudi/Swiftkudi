<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XPHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'xp_amount',
        'action_type',
        'description',
        'related_id',
    ];

    protected $casts = [
        'xp_amount' => 'integer',
    ];

    /**
     * Get the user this history belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
