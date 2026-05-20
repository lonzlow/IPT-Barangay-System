<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlotterEvidence extends Model
{
    /** @use HasFactory<\Database\Factories\BlotterEvidenceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'blotter_id',
        'file_path',
        'file_extension',
        'mime_type',
        'file_category',
        'caption',
    ];

    public function blotter(): BelongsTo
    {
        return $this->belongsTo(Blotter::class, 'blotter_id');
    }
}
