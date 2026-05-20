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

    public const DEFAULT_RECORD_TYPES = [
        'photo',
        'video',
        'activity',
        'accomplishment',
        'report',
        'attendance',
        'inventory',
        'partnership',
        'certificate',
    ];

    protected $fillable = [
        'name',
        'slug',
        'chairperson_id',
        'chair_label',
        'description',
        'allowed_record_types',
    ];

    protected $casts = [
        'allowed_record_types' => 'array',
    ];

    public function officials(): BelongsToMany
    {
        return $this->belongsToMany(Official::class, 'official_assignments')
            ->withPivot('designation')
            ->withTimestamps();
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
        return $this->hasMany(CommitteeRecord::class)->latest('recorded_at');
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

    public function allowedRecordTypes(): array
    {
        return $this->allowed_record_types ?: self::DEFAULT_RECORD_TYPES;
    }

    public function recordTypeLabels(): array
    {
        return collect($this->allowedRecordTypes())
            ->mapWithKeys(fn (string $type) => [$type => CommitteeRecord::TYPES[$type] ?? str($type)->headline()->toString()])
            ->all();
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
