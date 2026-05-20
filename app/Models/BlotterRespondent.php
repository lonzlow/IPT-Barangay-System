<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlotterRespondent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'blotter_id',
        'respondent_id',
        'respondent_name',
        'role',
    ];

    public function blotter(): BelongsTo
    {
        return $this->belongsTo(Blotter::class, 'blotter_id');
    }

    public function respondent(): BelongsTo
    {
        return $this->BelongsTo(Resident::class, 'respondent_id');
    }
}
