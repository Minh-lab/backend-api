<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class InternshipSearchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'student_code' => $this->student->usercode ?? 'N/A',
            'full_name'    => $this->student->full_name ?? 'N/A',
            'class_name'   => $this->student->studentClass->class_name ?? 'N/A',
            'company_name' => $this->company->name ?? 'N/A',
            'lecturer_name' => $this->lecturer->full_name ?? 'N/A', // Hiển thị cho VPK
            'status'       => $this->status,
            'final_grade'  => $this->university_grade ?? $this->company_grade ?? 0,
        ];
    }
}
