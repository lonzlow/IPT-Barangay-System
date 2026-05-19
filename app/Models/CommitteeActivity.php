<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommitteeActivity extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'committee_id',
        'activity_type',
        'title',
        'description',
        'activity_date',
        'location',
        'participants_count',
        'notes',
    ];

    protected $casts = [
        'activity_date' => 'datetime',
    ];

    /**
     * Get the committee for this activity
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }
}
