<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalServiceAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'name',
        'description',
        'price',
        'delivery_days_extra',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the service this addon belongs to
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(ProfessionalService::class, 'service_id');
    }
}
