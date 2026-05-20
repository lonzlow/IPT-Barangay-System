<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blotter extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'case_number',
        'complainant_id',
        'complainant_name',
        'respondent_id',
        'respondent_name',
        'location',
        'incident_description',
        'incident_date',
        'handled_by',
        'status',
        'filed_by',
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Get the user who filed this blotter
     */
    public function filedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('incident_date', [$startDate, $endDate]);
    }

    /**
     * Check if blotter is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if blotter is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
