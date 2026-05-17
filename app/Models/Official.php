<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Official extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'resident_id',
        'position',
        'designation',
        'term_start',
        'term_end',
        'status',
        'role_id',
        'digital_id',
        'contact_number',
        'email',
    ];

    protected $casts = [
        'term_start' => 'date',
        'term_end' => 'date',
    ];

    /**
     * Get the resident associated with this official
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * Get the role of this official
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get all assignments for this official
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(OfficialAssignment::class);
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
