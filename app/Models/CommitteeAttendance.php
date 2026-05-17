<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommitteeAttendance extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'committee_id',
        'meeting_title',
        'meeting_date',
        'attendees_count',
        'attendance_sheet_url',
        'notes',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
    ];

    /**
     * Get the committee for this attendance
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }
}
