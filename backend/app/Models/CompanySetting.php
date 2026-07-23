<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name', 'address', 'gst_number', 'vat_number',
        'tax_registration_number', 'contact_email', 'contact_phone', 'website',
        'bank_name', 'bank_account_name', 'bank_account_number',
        'bank_ifsc_code', 'bank_swift_code', 'bank_branch',
        'signatory_name', 'signature_image', 'company_seal',
    ];
}
