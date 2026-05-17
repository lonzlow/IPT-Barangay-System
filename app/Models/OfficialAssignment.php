<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficialAssignment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'official_id',
        'assignment_type',
        'description',
        'assigned_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the official for this assignment
     */
    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class);
    }

    /**
     * Check if assignment is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && now()->between($this->assigned_date, $this->end_date ?? now()->addYears(100));
    }
}
