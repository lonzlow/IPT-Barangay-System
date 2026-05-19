<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'documents';
    protected $fillable = [
        'resident_id',
        'document_template_id',
        'reference_number',
        'purpose',
        'rendered_html',
        'issued_by',
        'issued_date',
        'valid_until',
        'status',
        'seal_path',
        'signature_path',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'valid_until' => 'date',
    ];

    /**
     * Get the resident associated with this document
     */
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * Get the template used for this document
     */
    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    /**
     * Check if document is still valid
     */
    public function isValid()
    {
        if ($this->status === 'Revoked') {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Generate a unique reference number
     */
    public static function generateReferenceNumber($documentType = 'DOC')
    {
        $year = now()->year;
        $month = now()->month;
        $count = Document::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
        
        return sprintf('%s-%d%02d-%05d', $documentType, $year, $month, $count);
    }
}
