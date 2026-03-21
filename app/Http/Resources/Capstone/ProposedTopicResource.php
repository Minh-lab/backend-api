<?php

namespace App\Http\Resources\Capstone;

use Illuminate\Http\Resources\Json\JsonResource;

class ProposedTopicResource extends JsonResource
{
    public function toArray($request): array
    {
        $capstone = $this->capstone;
        $student = $capstone->student;
        $proposedTopic = $this->proposedTopic;

        return [
            'request_id'   => $this->capstone_request_id,
            'student_code' => $student->usercode ?? 'N/A',
            'student_name' => $student->full_name ?? 'N/A',
            'class_name'   => $student->studentClass->class_name ?? 'N/A',
            'topic_title'  => $proposedTopic->proposed_title ?? ($this->topic->title ?? 'N/A'),
            'technologies' => $proposedTopic->technologies ?? ($this->topic->technologies ?? null),
            'description'  => $proposedTopic->proposed_description ?? 'N/A',
            'student_message' => $this->student_message,
            'lecturer_feedback' => $this->lecturer_feedback,
            'status'       => $this->status,
            'created_at'   => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
