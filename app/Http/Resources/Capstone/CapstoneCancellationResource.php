<?php

namespace App\Http\Resources\Capstone;

use Illuminate\Http\Resources\Json\JsonResource;

class CapstoneCancellationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'capstone_id'   => $this->capstone_id,
            'student_code'  => $this->student->usercode ?? 'N/A',
            'student_name'  => $this->student->full_name ?? 'N/A',
            'class_name'    => $this->student->studentClass->class_name ?? 'N/A',
            'topic_title'   => $this->topic->title ?? 'Đang cập nhật',
            'current_status' => $this->status,
            'request_date'  => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
