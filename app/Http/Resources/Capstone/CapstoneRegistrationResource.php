<?php

namespace App\Http\Resources\Capstone;

use Illuminate\Http\Resources\Json\JsonResource;

class CapstoneRegistrationResource extends JsonResource
{
    public function toArray($request): array
    {
        $student = $this->capstone->student;
        return [
            'request_id'      => $this->capstone_request_id,
            'student_code'    => $student->usercode ?? 'N/A',
            'student_name'    => $student->full_name ?? 'N/A',
            'class'           => $student->studentClass->class_name ?? 'N/A',
            'registration_at' => $this->created_at->format('Y-m-d H:i:s'),
            'student_message' => $this->student_message,
        ];
    }
}
