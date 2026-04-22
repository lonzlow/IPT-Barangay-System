<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermitRenewal extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'permit_id',
        'fee_paid',
        'renewal_date',
        'processed_by',
    ];

    public function business_permit(): BelongsTo
    {
        return $this->belongsTo(BusinessPermit::class, 'permit_id');
    }

    public function processed_by(): BelongsTo
    {
        return $this->belongsTo(Official::class, 'processed_by');
    }
}
