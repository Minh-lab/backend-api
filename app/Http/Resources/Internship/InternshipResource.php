<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class InternshipResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'internship_id' => $this->internship_id,
            'student_id'    => $this->student_id,
            'semester'      => $this->semester->semester_name ?? 'N/A',
            'status'        => $this->status,
            'company'       => $this->company ? [
                'name' => $this->company->name,
                'tax_code' => $this->company->usercode,
            ] : null,
            'lecturer'      => $this->lecturer ? [
                'full_name' => $this->lecturer->full_name,
            ] : null,
            // Lấy request đăng ký doanh nghiệp mới nhất (loại 1)
            'latest_request' => new InternshipRequestResource(
                $this->requests()->where('type', 1)->latest()->first()
            ),
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
