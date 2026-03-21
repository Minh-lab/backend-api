<?php

namespace App\Http\Resources\Capstone;

use Illuminate\Http\Resources\Json\JsonResource;

class CapstoneGradingResource extends JsonResource
{
    public function toArray($request): array
    {
        // Lấy báo cáo mới nhất để giảng viên xem (NFR-2)
        $latestReport = $this->reports()->latest()->first();

        return [
            'capstone_id'   => $this->capstone_id,
            'student_code'  => $this->student->usercode ?? 'N/A',
            'student_name'  => $this->student->full_name ?? 'N/A',
            'class_name'    => $this->student->studentClass->class_name ?? 'N/A',
            'topic_title'   => $this->topic->title ?? 'N/A',
            'expertise'     => $this->topic->expertise->name ?? 'N/A',
            'current_grade' => $this->instructor_grade,
            'feedback'      => $this->instructor_feedback,
            'status'        => $this->status,
            // Hỗ trợ Preview báo cáo cuối cùng
            'preview_url'   => $latestReport && $latestReport->file_path
                ? asset('storage/' . $latestReport->file_path)
                : null,
        ];
    }
}
