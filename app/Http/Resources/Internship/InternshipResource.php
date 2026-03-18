<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class InternshipResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'internship_id' => $this->internship_id,
            'student_id'    => $this->student_id,
            'semester'      => $this->semester->semester_name ?? 'N/A',
            'status'        => $this->status,
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
