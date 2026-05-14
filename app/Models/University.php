<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'domain', 'city', 'state', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function listings()
    {
        return $this->hasMany(\App\Models\Marketplace\Listing::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function findByEmailDomain(string $email): ?self
    {
        $domain = substr(strrchr($email, '@'), 1);
        return self::active()->where('domain', $domain)->first();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}