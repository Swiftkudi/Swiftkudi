<?php

namespace App\Models\Marketplace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketplaceFavourite extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'listing_id'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function listing()
    {
        return $this->belongsTo(MarketplaceListing::class);
    }
}