<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CapstoneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'request_id'      => $this->capstone_request_id,
            'student_name'    => $this->capstone->student->full_name ?? 'N/A',
            'topic_title'     => $this->topic->title ?? ($this->proposedTopic->title ?? 'Đề tài tự đề xuất'),
            'registration_date' => $this->created_at->format('Y-m-d H:i:s'),
            'status'          => $this->status,
            'student_message' => $this->student_message,
        ];
    }
}
