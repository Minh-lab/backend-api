<?php

namespace App\Http\Resources\Capstone;

use Illuminate\Http\Resources\Json\JsonResource;

class CapstoneReportDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $capstone = $this->capstone;
        $student = $capstone->student;
        $topic = $capstone->topic;

        return [
            'report_id'        => $this->report_id,
            'student_code'     => $student->usercode ?? 'N/A',
            'student_name'     => $student->full_name ?? 'N/A',
            'topic_title'      => $topic->title ?? 'N/A',
            'phase_name'       => $this->milestone->phase_name ?? 'N/A',
            'submission_date'  => $this->submission_date->format('Y-m-d H:i:s'),
            'status'           => $this->status,
            'lecturer_feedback' => $this->lecturer_feedback,
            // NFR-1: Hỗ trợ URL để xem trước file PDF
            'file_preview_url' => $this->file_path ? asset('storage/' . $this->file_path) : null,
        ];
    }
}
