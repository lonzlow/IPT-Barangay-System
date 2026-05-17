<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'contact_number',
        'birthdate',
        'gender',
        'civil_status',
        'voter_status',
        'residency_status',
        'household_id',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function head_household(): HasOne
    {
        return $this->hasOne(Household::class);
    }

    public function purok_leader(): HasOne
    {
        return $this->hasOne(Purok::class);
    }

    public function business_owner(): HasOne
    {
        return $this->hasOne(BusinessOwner::class);
    }

    /**
     * Get the resident's age calculated from birthdate
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birthdate) {
            return null;
        }
        return Carbon::parse($this->birthdate)->age;
    }

    /**
     * Get age group for the resident
     */
    public function getAgeGroupAttribute(): ?string
    {
        if (!$this->age) {
            return null;
        }
        if ($this->age < 13) {
            return '0-12';
        } elseif ($this->age < 18) {
            return '13-17';
        } elseif ($this->age < 60) {
            return '18-59';
        } else {
            return '60+';
        }
    }

    /**
     * Get all documents issued for this resident
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
