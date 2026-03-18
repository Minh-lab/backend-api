<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyInternshipResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'internship_id'    => $this->internship_id,
            'student_code'     => $this->student->usercode ?? 'N/A',
            'full_name'        => $this->student->full_name ?? 'N/A',
            'class_name'       => $this->student->class->class_name ?? 'N/A',
            'position'         => $this->position,
            'status'           => $this->status,
            'company_grade'    => $this->company_grade,
            'company_feedback' => $this->company_feedback,
            // Bước 4: Lấy báo cáo mới nhất để xem trước (Preview)
            'latest_report'    => $this->reports()->latest()->first()?->file_path ? asset('storage/' . $this->reports()->latest()->first()->file_path) : null,
        ];
    }
}
