<?php

namespace App\Http\Resources\Capstone;

use Illuminate\Http\Resources\Json\JsonResource;

class CapstoneStatisticsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'student_code'    => $this->student->usercode ?? 'N/A',
            'student_name'    => $this->student->full_name ?? 'N/A',
            'class_name'      => $this->student->studentClass->class_name ?? 'N/A',
            'supervisor'      => $this->lecturer->full_name ?? 'Chưa phân công',
            // Lấy danh sách GVPB (phản biện) nối thành chuỗi
            'reviewers'       => $this->reviewers->map(fn($rev) => $rev->lecturer->full_name)->implode(', ') ?: 'Chưa phân công',
            'council'         => $this->council->name ?? 'Chưa có',
            'status'          => $this->status,
            // Sử dụng helper getFinalGradeAttribute đã có trong Model Capstone
            'final_grade'     => $this->final_grade ?? 'Chưa có điểm',
        ];
    }
}
