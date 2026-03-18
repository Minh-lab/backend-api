<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessStudentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'internship_id' => $this->internship_id,
            'student_code'  => $this->student->usercode ?? 'N/A',
            'full_name'     => $this->student->full_name ?? 'N/A',
            'class_name'    => $this->student->class->class_name ?? 'N/A',
            'position'      => $this->position,
            'status'        => $this->status,
            'created_at'    => $this->created_at ? $this->created_at->format('d/m/Y H:i') : 'N/A',
        ];
    }
}
