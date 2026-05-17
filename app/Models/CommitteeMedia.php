<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommitteeMedia extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'committee_id',
        'media_type',
        'media_url',
        'title',
        'description',
        'media_date',
        'uploaded_by_id',
    ];

    protected $casts = [
        'media_date' => 'datetime',
    ];

    /**
     * Get the committee for this media
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Get the user who uploaded this media
     */
    public function uploaded_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    /**
     * Scope to filter by media type (photo, video)
     */
    public function scopeByType($query, $type)
    {
        return $query->where('media_type', $type);
    }
}
