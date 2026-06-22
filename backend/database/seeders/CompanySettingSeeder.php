<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        CompanySetting::create([
            'company_name' => 'RFI Global ERP Solutions Pvt. Ltd.',
            'address' => '123 Business Park, Commercial Street, Mumbai - 400001, Maharashtra, India',
            'gst_number' => '27AAACR1234A1Z5',
            'vat_number' => 'VAT-12345678',
            'tax_registration_number' => 'PAN-AAAAA1234A',
            'contact_email' => 'finance@rfiglobal.com',
            'contact_phone' => '+91 22 4123 4567',
            'website' => 'www.rfiglobal.com',
            'bank_name' => 'State Bank of India',
            'bank_account_name' => 'RFI Global ERP Solutions Pvt. Ltd.',
            'bank_account_number' => '12345678901234',
            'bank_ifsc_code' => 'SBIN0001234',
            'bank_swift_code' => 'SBININBB123',
            'bank_branch' => 'Mumbai Main Branch',
            'signatory_name' => 'Authorised Signatory',
        ]);
    }
}
