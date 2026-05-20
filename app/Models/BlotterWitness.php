<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlotterWitness extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'blotter_id',
        'witness_id',
        'witness_name',
    ];

    public function blotter(): BelongsTo
    {
        return $this->belongsTo(Blotter::class, 'blotter_id');
    }

    public function resident_witness(): BelongsTo
    {
        return $this->BelongsTo(Resident::class, 'witness_id');
    }

    public function witness(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'witness_id');
    }
}
