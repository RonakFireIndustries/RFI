<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeDocumentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:10240',
            ],
            'document_type' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'file.mimes' => 'Only PDF, JPG, JPEG, PNG, and WEBP files are allowed. Executable and script files are strictly blocked.',
            'file.max' => 'The file must not exceed 10 MB.',
        ];
    }
}
