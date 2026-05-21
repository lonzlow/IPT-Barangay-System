<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficialAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'official_id',
        'committee_id',
        'designation',
    ];

    protected $casts = [
        'committee_id' => 'integer',
    ];

    /**
     * Get the official for this assignment
     */
    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class);
    }

    /**
     * Get the committee for this assignment
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }
}
