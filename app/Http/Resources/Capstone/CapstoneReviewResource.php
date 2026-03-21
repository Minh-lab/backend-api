<?php

namespace App\Http\Resources\Capstone;

use Illuminate\Http\Resources\Json\JsonResource;

class CapstoneReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        $capstone = $this->capstone;
        $student = $capstone->student;
        // Lấy báo cáo mới nhất để phản biện xem
        $latestReport = $capstone->reports()->latest()->first();

        return [
            'capstone_id'   => $this->capstone_id,
            'student_code'  => $student->usercode ?? 'N/A',
            'student_name'  => $student->full_name ?? 'N/A',
            'class_name'    => $student->studentClass->class_name ?? 'N/A',
            'topic_title'   => $capstone->topic->title ?? 'N/A',
            'expertise'     => $capstone->topic->expertise->name ?? 'N/A',
            'my_grade'      => $this->opponent_grade,
            'feedback'      => $this->opponent_feedback,
            'status'        => $capstone->status,
            'preview_url'   => $latestReport ? asset('storage/' . $latestReport->file_path) : null,
        ];
    }
}
