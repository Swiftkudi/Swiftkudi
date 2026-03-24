<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderAddOn extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'name',
        'price',
        'description',
        'is_included',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_included' => 'boolean',
    ];

    /**
     * Get the order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'order_id');
    }
}
