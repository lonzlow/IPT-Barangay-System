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
        "head_first_name",
        "head_middle_name",
        "head_last_name",
        "head_contact_number",
        "notes",
        "status",
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
