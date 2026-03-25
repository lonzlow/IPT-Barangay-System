<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessOwner extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessOwnerFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'owner_type',
        'resident_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'organization_name',
        'contact_number',
        'email',
        'address',
    ];

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class,
        'business_owner_business', 'business_owner_id', 'business_id');
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }
}
