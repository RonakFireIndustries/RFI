<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'emp_id' => 'required|string|max:255|unique:employees,emp_id',
            'full_name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'contact_number' => 'nullable|string|max:20',
            'personal_number' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',

            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'employment_type' => 'required|string',
            'reporting_manager_id' => 'nullable|exists:employees,id',
            'joining_date' => 'required|date',
            'interview_date' => 'nullable|date',
            'probation_end_date' => 'nullable|date',

            'government_id_type' => 'nullable|in:Aadhaar,PAN,Passport,Voter ID',
            'qualification' => 'nullable|string|max:255',
            'employment_bond_status' => 'nullable|boolean',
            'previous_termination_status' => 'nullable|boolean',
            'legal_proceedings_status' => 'nullable|boolean',
            'create_user_account' => 'nullable|boolean',
            'role' => 'nullable|string|exists:roles,name',

            'photo' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'aadhaar' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'pan' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'offer_letter' => 'nullable|file|mimes:pdf|max:5120',
        ];
    }
}
