<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Committee extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'description',
        'responsible_official_id',
        'status',
        'established_date',
    ];

    protected $casts = [
        'established_date' => 'date',
    ];

    /**
     * Get the official responsible for this committee
     */
    public function responsible_official(): BelongsTo
    {
        return $this->belongsTo(Official::class, 'responsible_official_id');
    }

    /**
     * Get all committee activities
     */
    public function activities(): HasMany
    {
        return $this->hasMany(CommitteeActivity::class);
    }

    /**
     * Get all committee accomplishments
     */
    public function accomplishments(): HasMany
    {
        return $this->hasMany(CommitteeAccomplishment::class);
    }

    /**
     * Get all committee media
     */
    public function media(): HasMany
    {
        return $this->hasMany(CommitteeMedia::class);
    }

    /**
     * Get all committee records
     */
    public function records(): HasMany
    {
        return $this->hasMany(CommitteeRecord::class);
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
