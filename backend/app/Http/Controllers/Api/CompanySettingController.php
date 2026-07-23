<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function show()
    {
        $this->authorize('manage settings');
        $settings = CompanySetting::first();
        if (!$settings) {
            $settings = CompanySetting::create([]);
        }
        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('manage settings');
        $settings = CompanySetting::first();
        if (!$settings) {
            $settings = CompanySetting::create([]);
        }

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:50',
            'tax_registration_number' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_ifsc_code' => 'nullable|string|max:50',
            'bank_swift_code' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',
            'signatory_name' => 'nullable|string|max:255',
            'signature_image' => 'nullable|image|max:2048',
            'company_seal' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('signature_image')) {
            if ($settings->signature_image) {
                Storage::disk('public')->delete($settings->signature_image);
            }
            $validated['signature_image'] = $request->file('signature_image')->store('company', 'public');
        }

        if ($request->hasFile('company_seal')) {
            if ($settings->company_seal) {
                Storage::disk('public')->delete($settings->company_seal);
            }
            $validated['company_seal'] = $request->file('company_seal')->store('company', 'public');
        }

        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Company settings updated successfully',
            'data' => $settings->fresh(),
        ]);
    }
}
