<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class InternshipDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        // Tính điểm tổng kết nếu cả hai điểm đều có
        $finalScore = null;
        if (!is_null($this->company_grade) && !is_null($this->university_grade)) {
            $finalScore = ($this->company_grade + $this->university_grade) / 2;
        }

        return [
            'internship_id' => $this->internship_id,
            'student' => $this->student ? [
                'student_id' => $this->student->student_id,
                'usercode' => $this->student->usercode,
                'full_name' => $this->student->full_name,
                'email' => $this->student->email,
                'class' => $this->student->studentClass ? [
                    'class_id' => $this->student->studentClass->class_id,
                    'class_name' => $this->student->studentClass->class_name,
                ] : null,
            ] : null,
            'company' => $this->company ? [
                'company_id' => $this->company->company_id,
                'name' => $this->company->name,
                'tax_code' => $this->company->usercode,
                'email' => $this->company->email,
                'address' => $this->company->address,
                'website' => $this->company->website ?? null,
            ] : null,
            'lecturer' => $this->lecturer ? [
                'lecturer_id' => $this->lecturer->lecturer_id,
                'full_name' => $this->lecturer->full_name,
                'email' => $this->lecturer->email,
            ] : null,
            'semester' => $this->semester ? [
                'semester_id' => $this->semester->semester_id,
                'name' => $this->semester->name,
            ] : null,
            'status' => $this->status,
            'position' => $this->position,
            'company_grade' => $this->company_grade,
            'company_feedback' => $this->company_feedback,
            'university_grade' => $this->university_grade,
            'university_feedback' => $this->university_feedback,
            'final_grade' => $finalScore,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'reports' => $this->reports ? $this->reports->map(fn($report) => [
                'report_id' => $report->report_id,
                'title' => $report->title,
                'submitted_date' => $report->submitted_date,
                'status' => $report->status,
                'file_url' => $report->file_url ?? null,
            ])->toArray() : [],
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
