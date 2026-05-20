<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Household extends Model
{
    use HasFactory;

    protected $fillable = [
        "purok_id",
        "house_number",
        "street",
        "family_size",
        "head_resident_id",
    ];

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function head_resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, "head_resident_id");
    }

    public function purok(): BelongsTo
    {
        return $this->belongsTo(Purok::class);
    }
}
