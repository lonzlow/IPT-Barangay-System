<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessPermit extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'business_id',
        'permit_number',
        'expiry_date',
        'permit_status',
        'issued_by',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function issued_by(): BelongsTo
    {
        return $this->belongsTo(Official::class, 'issued_by');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(PermitRenewal::class, 'permit_id');
    }
}
