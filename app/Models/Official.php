<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Official extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'official_number',
        'resident_id',
        'role_id',
        'committee_id',
        'term_start',
        'term_end',
        'is_active',
    ];

    protected $casts = [
        'term_start' => 'date',
        'term_end' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the resident associated with this official
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(HasOne::class, 'official_id');
    }

    /**
     * Get all assignments for this official
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(OfficialAssignment::class);
    }

    public function committees(): BelongsToMany
    {
        return $this->belongsToMany(Committee::class, 'official_assignments')
            ->withPivot('designation')
            ->withTimestamps();
    }

    /**
     * Get signature images for this official
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(\App\Models\Signature::class);
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role?->role_name === $roleName;
    }

    public function hasAnyRole(array $roleNames): bool
    {
        return in_array($this->role?->role_name, $roleNames, true);
    }

    /**
     * Check if official term is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && now()->between($this->term_start, $this->term_end);
    }

    /**
     * Check if official term has ended
     */
    public function hasTermEnded(): bool
    {
        return now()->isAfter($this->term_end);
    }

    /**
     * Get days remaining in term
     */
    public function daysRemainingInTerm(): int
    {
        return $this->term_end->diffInDays(now());
    }

    /**
     * Scope to filter by status
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by position
     */
    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope to filter by current term
     */
    public function scopeCurrentTerm($query)
    {
        return $query->where('status', 'active')
            ->where('term_start', '<=', now())
            ->where('term_end', '>=', now());
    }
}
