<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class CancelRequestDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $student = $this->internship->student;
        return [
            'request_id'      => $this->internship_request_id,
            'student_code'    => $student->usercode ?? 'N/A',
            'student_name'    => $student->full_name ?? 'N/A',
            'class'           => $student->class->class_name ?? 'N/A',
            'reason'          => $this->student_message,
            'current_status'  => $this->status,
            'created_at'      => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
