<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DocumentTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'document_templates';
    protected $fillable = [
        'name',
        'description',
        'template_html',
        'fields_required',
        'validity_days',
        'is_active',
    ];

    protected $casts = [
        'fields_required' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get all documents created from this template
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Render the template with resident data
     */
    public function render($resident)
    {
        $html = $this->template_html;
        
        // Helper to format date - handle both Carbon and string dates
        $formatDate = function($date, $format = 'F d, Y') {
            if (!$date) return 'N/A';
            if (is_string($date)) {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format($format);
            }
            return $date->format($format);
        };
        
        // Extract resident data
        $data = [
            '{{resident_name}}' => $resident->first_name . ' ' . ($resident->middle_name ? $resident->middle_name[0] . '. ' : '') . $resident->last_name . ($resident->suffix ? ' ' . $resident->suffix : ''),
            '{{first_name}}' => $resident->first_name,
            '{{middle_name}}' => $resident->middle_name ?? '',
            '{{last_name}}' => $resident->last_name,
            '{{suffix}}' => $resident->suffix ?? '',
            '{{age}}' => $resident->age ?? 'N/A',
            '{{birthdate}}' => $formatDate($resident->birthdate),
            '{{gender}}' => $resident->gender ?? 'N/A',
            '{{civil_status}}' => $resident->civil_status ?? 'N/A',
            '{{email}}' => $resident->email ?? 'N/A',
            '{{contact_number}}' => $resident->contact_number ?? 'N/A',
            '{{voter_status}}' => $resident->voter_status ?? 'N/A',
            '{{residency_status}}' => $resident->residency_status ?? 'N/A',
            '{{household_address}}' => $resident->household?->purok?->name ?? 'N/A',
            '{{current_date}}' => now()->format('F d, Y'),
            '{{current_date_long}}' => now()->format('l, F d, Y'),
        ];

        // Replace placeholders with actual data
        foreach ($data as $placeholder => $value) {
            $html = str_replace($placeholder, $value, $html);
        }

        return $html;
    }
}
