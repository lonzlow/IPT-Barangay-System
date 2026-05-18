<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommitteeRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'committees_records';

    protected $fillable = [
        'committee_id',
        'record_type',
        'title',
        'description',
        'file_path',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'record_type' => 'string',
    ];

    /**
     * Get the committee for this record
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Scope to filter by record type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('record_type', $type);
    }
}
