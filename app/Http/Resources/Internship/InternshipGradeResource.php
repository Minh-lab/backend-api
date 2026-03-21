<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class InternshipGradeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'internship_id'   => $this->internship_id,
            'student_code'    => $this->student->usercode ?? 'N/A',
            'student_name'    => $this->student->full_name ?? 'N/A',
            'class_name'      => $this->student->studentClass->class_name ?? 'N/A',
            'company_grade'   => $this->company_grade, // Điểm quá trình (từ doanh nghiệp)
            'university_grade' => $this->university_grade, // Điểm thi (từ giảng viên)
            'university_feedback' => $this->university_feedback,
            'final_grade'     => $this->calculateFinalGrade(),
            'status'          => $this->status,
            'report_preview'  => $this->reports()->latest()->first()->file_path ?? null,
        ];
    }

    private function calculateFinalGrade()
    {
        if (is_null($this->company_grade) || is_null($this->university_grade)) {
            return null;
        }
        return ($this->company_grade + $this->university_grade) / 2;
    }
}
