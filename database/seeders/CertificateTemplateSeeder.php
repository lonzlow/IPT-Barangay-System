<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class CertificateTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Barangay Indigency Certificate',
                'description' => 'Certificate stating that a resident is indigent and in need of government assistance.',
                'template_html' => $this->getIndigencyCertificate(),
                'fields_required' => ['first_name', 'last_name', 'household_address'],
                'validity_days' => 180,
            ],
            [
                'name' => 'Barangay Residency Certificate',
                'description' => 'Certificate certifying the residency status of a barangay resident.',
                'template_html' => $this->getResidencyCertificate(),
                'fields_required' => ['resident_name', 'household_address', 'contact_number'],
                'validity_days' => 365,
            ],
            [
                'name' => 'Barangay Clearance',
                'description' => 'General clearance certificate for employment, travel, or business purposes.',
                'template_html' => $this->getClearanceCertificate(),
                'fields_required' => ['resident_name', 'age', 'gender', 'household_address'],
                'validity_days' => 90,
            ],
            [
                'name' => 'Good Moral Character Certificate',
                'description' => 'Certificate attesting to the good moral character of a resident.',
                'template_html' => $this->getGoodMoralCharacterCertificate(),
                'fields_required' => ['resident_name', 'age', 'civil_status', 'household_address'],
                'validity_days' => 180,
            ],
            [
                'name' => 'Business Clearance Certificate',
                'description' => 'Clearance for business operations and compliance with barangay regulations.',
                'template_html' => $this->getBusinessClearanceCertificate(),
                'fields_required' => ['resident_name', 'household_address'],
                'validity_days' => 365,
            ],
        ];

        foreach ($templates as $template) {
            DocumentTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }

    private function getIndigencyCertificate()
    {
        return <<<'HTML'
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; text-align: center; line-height: 1.6; }
        .header { margin-bottom: 30px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .content { text-align: justify; margin: 30px 0; }
        .reference { text-align: right; margin: 20px 0; font-size: 12px; }
        .footer { margin-top: 50px; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin: 50px auto; text-align: center; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">BARANGAY INDIGENCY CERTIFICATE</div>
    </div>

    <div class="reference">
        <strong>Reference No:</strong> {{reference_number}}<br>
        <strong>Date Issued:</strong> {{current_date_long}}
    </div>

    <div class="content">
        <p>TO WHOM IT MAY CONCERN:</p>

        <p>This is to certify that <strong>{{resident_name}}</strong>, of legal age, residing at 
        <strong>{{household_address}}</strong>, is a bonafide resident of this barangay.</p>

        <p>This is to further certify that based on the assessment and investigation conducted by this office, 
        the above-named individual is of indigent status and in need of financial/social assistance from the government.</p>

        <p>This certificate is being issued upon the request of the aforementioned person for whatever purpose(s) it may serve.</p>

        <p><em>This certificate is valid for a period of six (6) months from the date of issuance.</em></p>

        <p>IN WITNESS WHEREOF, I have hereunto set my hand this {{current_date}}.</p>
    </div>

    <div class="footer">
        <div class="signature-line">{{issued_by}}</div>
        <p><strong>Barangay Official</strong></p>
    </div>
</body>
</html>
HTML;
    }

    private function getResidencyCertificate()
    {
        return <<<'HTML'
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; text-align: center; line-height: 1.6; }
        .header { margin-bottom: 30px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .content { text-align: justify; margin: 30px 0; }
        .reference { text-align: right; margin: 20px 0; font-size: 12px; }
        .footer { margin-top: 50px; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin: 50px auto; text-align: center; padding-top: 5px; }
        .details { margin: 20px 0; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">BARANGAY RESIDENCY CERTIFICATE</div>
    </div>

    <div class="reference">
        <strong>Reference No:</strong> {{reference_number}}<br>
        <strong>Date Issued:</strong> {{current_date_long}}
    </div>

    <div class="content">
        <p>TO WHOM IT MAY CONCERN:</p>

        <p>This is to certify that <strong>{{resident_name}}</strong>, whose personal details are as follows:</p>

        <div class="details">
            <p><strong>Contact Number:</strong> {{contact_number}}</p>
            <p><strong>Address:</strong> {{household_address}}</p>
            <p><strong>Residency Status:</strong> {{residency_status}}</p>
        </div>

        <p>is a bonafide resident of this barangay as confirmed in our community record.</p>

        <p>This certificate is being issued upon the request of the aforementioned person for employment, 
        travel, business, educational, or any other lawful purpose.</p>

        <p><em>This certificate is valid for one (1) year from the date of issuance.</em></p>

        <p>IN WITNESS WHEREOF, I have hereunto set my hand this {{current_date}}.</p>
    </div>

    <div class="footer">
        <div class="signature-line">{{issued_by}}</div>
        <p><strong>Barangay Official</strong></p>
    </div>
</body>
</html>
HTML;
    }

    private function getClearanceCertificate()
    {
        return <<<'HTML'
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; text-align: center; line-height: 1.6; }
        .header { margin-bottom: 30px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .content { text-align: justify; margin: 30px 0; }
        .reference { text-align: right; margin: 20px 0; font-size: 12px; }
        .footer { margin-top: 50px; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin: 50px auto; text-align: center; padding-top: 5px; }
        .details { margin: 20px 0; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">BARANGAY CLEARANCE</div>
    </div>

    <div class="reference">
        <strong>Reference No:</strong> {{reference_number}}<br>
        <strong>Date Issued:</strong> {{current_date_long}}
    </div>

    <div class="content">
        <p>TO WHOM IT MAY CONCERN:</p>

        <p>This is to certify that <strong>{{resident_name}}</strong>, {{age}} years old, {{gender}}, 
        residing at <strong>{{household_address}}</strong>, has been cleared by this Barangay Office.</p>

        <p>The herein-named person is cleared and found to be in good standing with the Barangay. 
        He/She has no criminal record, delinquency, or any case pending in our office.</p>

        <p>This clearance is being issued for purposes of employment, travel, education, business, 
        or any other lawful use.</p>

        <p><em>This clearance is valid for three (3) months from the date of issuance.</em></p>

        <p>IN WITNESS WHEREOF, I have hereunto set my hand this {{current_date}}.</p>
    </div>

    <div class="footer">
        <div class="signature-line">{{issued_by}}</div>
        <p><strong>Barangay Official</strong></p>
    </div>
</body>
</html>
HTML;
    }

    private function getGoodMoralCharacterCertificate()
    {
        return <<<'HTML'
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; text-align: center; line-height: 1.6; }
        .header { margin-bottom: 30px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .content { text-align: justify; margin: 30px 0; }
        .reference { text-align: right; margin: 20px 0; font-size: 12px; }
        .footer { margin-top: 50px; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin: 50px auto; text-align: center; padding-top: 5px; }
        .details { margin: 20px 0; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">GOOD MORAL CHARACTER CERTIFICATE</div>
    </div>

    <div class="reference">
        <strong>Reference No:</strong> {{reference_number}}<br>
        <strong>Date Issued:</strong> {{current_date_long}}
    </div>

    <div class="content">
        <p>TO WHOM IT MAY CONCERN:</p>

        <p>This is to certify that <strong>{{resident_name}}</strong>, {{age}} years old, 
        {{civil_status}}, residing at <strong>{{household_address}}</strong>, is a resident of this barangay.</p>

        <p>Based on the records of this office and community assessment, the aforementioned person 
        has maintained good moral character and is known to be honest, industrious, and of good reputation 
        in the community.</p>

        <p>This certificate is issued to attest to the good moral character of the named person 
        for the purpose of employment, education, legal proceedings, travel, or any other lawful purpose.</p>

        <p><em>This certificate is valid for six (6) months from the date of issuance.</em></p>

        <p>IN WITNESS WHEREOF, I have hereunto set my hand this {{current_date}}.</p>
    </div>

    <div class="footer">
        <div class="signature-line">{{issued_by}}</div>
        <p><strong>Barangay Official</strong></p>
    </div>
</body>
</html>
HTML;
    }

    private function getBusinessClearanceCertificate()
    {
        return <<<'HTML'
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; text-align: center; line-height: 1.6; }
        .header { margin-bottom: 30px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .content { text-align: justify; margin: 30px 0; }
        .reference { text-align: right; margin: 20px 0; font-size: 12px; }
        .footer { margin-top: 50px; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin: 50px auto; text-align: center; padding-top: 5px; }
        .details { margin: 20px 0; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">BUSINESS CLEARANCE CERTIFICATE</div>
    </div>

    <div class="reference">
        <strong>Reference No:</strong> {{reference_number}}<br>
        <strong>Date Issued:</strong> {{current_date_long}}
    </div>

    <div class="content">
        <p>TO WHOM IT MAY CONCERN:</p>

        <p>This is to certify that <strong>{{resident_name}}</strong>, residing at 
        <strong>{{household_address}}</strong>, has secured clearance from the Barangay Office 
        for business operations within the jurisdiction of this barangay.</p>

        <p>The applicant has complied with all barangay requirements and regulations regarding 
        business operations and has no outstanding violations or violations pending.</p>

        <p>This clearance is issued in accordance with barangay ordinances and regulations governing 
        business activities within this jurisdiction.</p>

        <p><em>This clearance is valid for one (1) year from the date of issuance and is non-transferable.</em></p>

        <p>IN WITNESS WHEREOF, I have hereunto set my hand this {{current_date}}.</p>
    </div>

    <div class="footer">
        <div class="signature-line">{{issued_by}}</div>
        <p><strong>Barangay Official</strong></p>
    </div>
</body>
</html>
HTML;
    }
}
