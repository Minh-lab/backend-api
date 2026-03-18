<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        // Truy xuất thông tin từ quan hệ Internship
        $internship = $this->internship;
        $student = $internship->student;
        $company = $internship->company ?? $internship->proposedCompany;

        return [
            'report_id'        => $this->report_id,
            'student_code'     => $student->usercode ?? 'N/A',
            'student_name'     => $student->full_name ?? 'N/A',
            'position'         => $internship->internshipRequest->student_message ?? 'N/A', // Vị trí thực tập
            'company_name'     => $company->name ?? 'N/A',
            'phase_name'       => $this->milestone->phase_name ?? 'N/A',
            'description'      => $this->description,
            'file_preview_url' => $this->file_path ? asset('storage/' . $this->file_path) : null, // NFR-1
            'status'           => $this->status,
            'submitted_at'     => $this->submission_date->format('Y-m-d H:i:s'),
        ];
    }
}
