<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purok extends Model
{
    use HasFactory;

    protected $fillable = [
        'purok_name',
        'description',
        'leader_id',
    ];

    public function households(): HasMany
    {
        return $this->hasMany(Household::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }
}
