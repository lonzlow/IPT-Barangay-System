<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommitteeAccomplishment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'committee_id',
        'title',
        'description',
        'impact',
        'accomplished_date',
        'status',
    ];

    protected $casts = [
        'accomplished_date' => 'date',
    ];

    /**
     * Get the committee for this accomplishment
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }
}
