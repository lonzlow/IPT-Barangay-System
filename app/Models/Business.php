<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'business_name',
        'business_type',
        'business_address',
        'status',
    ];

    public function business_owners(): BelongsToMany
    {
        return $this->belongsToMany(BusinessOwner::class,
        'business_owner_business', 'business_id', 'business_owner_id');
    }

    public function business_permits(): HasMany
    {
        return $this->hasMany(BusinessPermit::class);
    }
}
