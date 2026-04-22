<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Committee extends Model
{
    /** @use HasFactory<\Database\Factories\CommitteeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'committee_name',
        'description',
        'chairperson_id',
    ];

    public function officials(): HasMany
    {
        return $this->hasMany(Official::class, 'committee_id');
    }

    public function chairperson(): BelongsTo
    {
        return $this->belongsTo(Official::class, 'chairperson_id');
    }
}
