<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Signature extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'signatures';

    protected $fillable = [
        'official_id',
        'label',
        'path',
        'uploaded_by',
    ];

    /**
     * The official who owns this signature image
     */
    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Public URL for browser preview/print (requires storage:link).
     */
    public function publicUrl(): string
    {
        if (!$this->path) {
            return '';
        }

        return Storage::disk('public')->url($this->path);
    }

    /**
     * Absolute filesystem path for DomPDF and other local renderers.
     */
    public function absolutePath(): ?string
    {
        if (!$this->path || !Storage::disk('public')->exists($this->path)) {
            return null;
        }

        return Storage::disk('public')->path($this->path);
    }
}
