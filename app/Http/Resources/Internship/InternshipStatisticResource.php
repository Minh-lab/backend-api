<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class InternshipStatisticResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'student_code' => $this->student->usercode ?? 'N/A',
            'full_name'    => $this->student->full_name ?? 'N/A',
            'class_name'   => $this->student->class->class_name ?? 'N/A',
            'company_name' => $this->company->name ?? 'N/A',
            'lecturer_name' => $this->lecturer->full_name ?? 'N/A',
            'status'       => $this->status,
            'company_grade'   => $this->company_grade,   // Điểm quá trình
            'university_grade' => $this->university_grade, // Điểm thi
        ];
    }
}
