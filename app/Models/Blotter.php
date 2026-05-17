<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blotter extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'blotter_number',
        'title',
        'description',
        'incident_date',
        'reported_by_id',
        'involved_parties',
        'status',
        'assigned_to_id',
        'case_location',
        'supporting_documents',
        'notes',
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'supporting_documents' => 'array',
    ];

    /**
     * Get the resident who reported the incident
     */
    public function reported_by(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'reported_by_id');
    }

    /**
     * Get the user assigned to this blotter
     */
    public function assigned_to(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
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
