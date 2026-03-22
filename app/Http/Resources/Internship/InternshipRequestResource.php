<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class InternshipRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'request_id'      => $this->internship_request_id,
            'status'          => $this->status,
            'company_name'    => $this->company->name ?? $this->proposedCompany->name ?? 'N/A',
            'tax_code'        => $this->company->usercode ?? $this->proposedCompany->tax_code ?? 'N/A',
            'email'           => $this->company->email ?? $this->proposedCompany->contact_email ?? 'N/A',
            'address'         => $this->company->address ?? $this->proposedCompany->address ?? 'N/A',
            'student_message' => $this->student_message,
            'file_url'        => $this->file_path ? asset('storage/' . $this->file_path) : null,
            'created_at'      => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
