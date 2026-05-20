<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommitteeRecord extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'photo' => 'Photo',
        'video' => 'Video',
        'activity' => 'Activity',
        'accomplishment' => 'Accomplishment',
        'report' => 'Report',
        'attendance' => 'Attendance',
        'inventory' => 'Inventory',
        'partnership' => 'Partnership',
        'certificate' => 'Certificate',
        'blotter_incident' => 'Incident / Blotter Record',
        'resolution_policy' => 'Resolution / Policy',
        'training_seminar' => 'Training / Seminar',
        'personnel_list' => 'Personnel List',
        'project_proposal' => 'Project Report / Proposal',
        'financial_record' => 'Financial Record',
        'permit_contract' => 'Permit / Contract',
        'driver_operator_profile' => 'Driver / Operator Profile',
        'emergency_log' => 'Emergency Communication Log',
        'evacuation_center_record' => 'Evacuation Center Record',
    ];

    protected $table = 'committees_records';

    protected $fillable = [
        'committee_id',
        'record_type',
        'category',
        'title',
        'description',
        'record_date',
        'quantity',
        'amount',
        'partner_name',
        'status',
        'metadata',
        'file_path',
        'uploaded_by_id',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'record_date' => 'date',
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'record_type' => 'string',
    ];

    /**
     * Get the committee for this record
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->record_type] ?? str($this->record_type)->headline()->toString();
    }

    /**
     * Scope to filter by record type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('record_type', $type);
    }
}
