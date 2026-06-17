<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'emp_id' => $this->emp_id,
            'full_name' => $this->full_name,
            'photo_path' => $this->photo_path,
            'interview_date' => $this->interview_date,
            'joining_date' => $this->joining_date,
            'probation_end_date' => $this->probation_end_date,
            'dob' => $this->dob,
            'department_id' => $this->department_id,
            'designation_id' => $this->designation_id,
            'employment_type' => $this->employment_type,
            'reporting_manager_id' => $this->reporting_manager_id,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'government_id_type' => $this->government_id_type,
            'address' => $this->address,
            'contact_number' => $this->contact_number,
            'personal_number' => $this->personal_number,
            'emergency_contact' => $this->emergency_contact,
            'qualification' => $this->qualification,
            'employment_bond_status' => (bool)$this->employment_bond_status,
            'previous_termination_status' => (bool)$this->previous_termination_status,
            'legal_proceedings_status' => (bool)$this->legal_proceedings_status,
            'resume_path' => $this->resume_path,
            'aadhaar_path' => $this->aadhaar_path,
            'pan_path' => $this->pan_path,
            'offer_letter_path' => $this->offer_letter_path,

            // Relations
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'designation' => new DesignationResource($this->whenLoaded('designation')),
            'manager' => new EmployeeResource($this->whenLoaded('manager')),
            'subordinates' => EmployeeResource::collection($this->whenLoaded('subordinates')),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'role' => $this->user->getRoleNames()->first(),
            ]),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
