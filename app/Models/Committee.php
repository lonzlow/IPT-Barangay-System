<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Committee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'chairperson_id',
        'description',
    ];

    public function officials(): BelongsToMany
    {
        return $this->belongsToMany(Official::class, 'official_id');
    }

    /**
     * Get the head official of this committee
     */
    public function headOfficial(): BelongsTo
    {
        return $this->belongsTo(Official::class, 'chairperson_id');
    }

    /**
     * Get all assignments for this committee
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(OfficialAssignment::class);
    }

    /**
     * Get all records for this committee
     */
    public function records(): HasMany
    {
        return $this->hasMany(CommitteeRecord::class);
    }


    /**
     * Get all committee media
     */
    public function media(): HasMany
    {
        return $this->hasMany(CommitteeMedia::class);
    }

    /**
     * Get all attendance sheets
     */
    public function attendance_sheets(): HasMany
    {
        return $this->hasMany(CommitteeAttendance::class);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by status
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
