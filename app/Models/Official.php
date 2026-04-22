<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Official extends Model
{
    /** @use HasFactory<\Database\Factories\OfficialFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'official_number',
        'resident_id',
        'role_id',
        'committee_id',
        'term_start',
        'term_end',
    ];

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function committee_chairperson(): HasOne
    {
        return $this->hasOne(Official::class, 'chairperson_id');
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class, 'committee_id');
    }

    public function business_permits(): HasMany
    {
        return $this->hasMany(BusinessPermit::class, 'issued_by');
    }
}
