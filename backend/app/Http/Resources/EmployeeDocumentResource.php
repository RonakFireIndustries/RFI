<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_file_name' => $this->original_file_name,
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'document_type' => $this->document_type,
            'expiry_date' => $this->expiry_date,
            'remarks' => $this->remarks,
            'uploaded_by' => [
                'id' => $this->uploader?->id,
                'name' => $this->uploader?->name,
            ],
            'employee_id' => $this->documentable_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
