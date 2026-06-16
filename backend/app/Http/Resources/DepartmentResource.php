<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'branch' => $this->whenLoaded('branch', fn () => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ]),
            'head' => $this->whenLoaded('head', fn () => [
                'id' => $this->head->id,
                'name' => $this->head->user->name ?? null,
            ]),
            'employees_count' => $this->whenLoaded('employees', fn () => $this->employees->count()),
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
