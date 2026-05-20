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
     * Render the template with resident, document, and optional business data.
     */
    public function render($resident, array $context = []): string
    {
        $html = $this->template_html;
        $business = $context['business'] ?? null;
        $issuedDate = $context['issued_date'] ?? now();
        $purpose = $context['purpose'] ?? null;
        $additionalNotes = $context['additional_notes'] ?? null;

        $formatDate = function ($date, $format = 'F d, Y') {
            if (!$date) {
                return 'N/A';
            }

            if (is_string($date)) {
                return \Carbon\Carbon::parse($date)->format($format);
            }

            return $date->format($format);
        };

        $residentName = trim(
            $resident->first_name . ' ' .
            ($resident->middle_name ? mb_substr($resident->middle_name, 0, 1) . '. ' : '') .
            $resident->last_name .
            ($resident->suffix ? ' ' . $resident->suffix : '')
        );

        $purokName = $resident->household?->purok?->purok_name ?? $resident->household?->purok?->name;
        $householdAddress = $resident->household
            ? trim(($resident->household->house_number ? $resident->household->house_number . ' ' : '') . $resident->household->street . ', ' . $purokName)
            : null;
        $notesHtml = $additionalNotes
            ? nl2br(e($additionalNotes))
            : 'No additional notes provided.';

        $data = [
            '{{reference_number}}' => $context['reference_number'] ?? 'PREVIEW',
            '{{purpose}}' => $purpose ?: 'any lawful purpose',
            '{{additional_notes}}' => $notesHtml,
            '{{issued_by}}' => $context['issued_by'] ?? 'Barangay Official',
            '{{seal_url}}' => $context['seal_url'] ?? asset('images/logo/Barangay New Era Logo.jpg'),
            '{{signature_url}}' => $context['signature_url'] ?? '',
            '{{resident_name}}' => $residentName,
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
            '{{household_address}}' => $householdAddress ?: 'N/A',
            '{{business_name}}' => $business?->business_name ?? 'N/A',
            '{{business_type}}' => $business?->business_type ?? 'N/A',
            '{{business_address}}' => $business?->business_address ?? 'N/A',
            '{{business_status}}' => $business?->status ?? 'N/A',
            '{{business_date_established}}' => $business?->date_established ? $formatDate($business->date_established) : 'N/A',
            '{{current_date}}' => $formatDate($issuedDate),
            '{{current_date_long}}' => $formatDate($issuedDate, 'l, F d, Y'),
        ];

        foreach ($data as $placeholder => $value) {
            $html = str_replace($placeholder, (string) $value, $html);
        }

        return $html;
    }
}
