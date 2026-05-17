<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommitteeRecord extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'committee_id',
        'record_type',
        'title',
        'content',
        'record_date',
        'reference_number',
    ];

    protected $casts = [
        'record_date' => 'datetime',
    ];

    /**
     * Get the committee for this record
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Scope to filter by record type (incident, resolution, training, partnership, attendance)
     */
    public function scopeByType($query, $type)
    {
        return $query->where('record_type', $type);
    }
}
