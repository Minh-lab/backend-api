<?php

namespace App\Http\Resources\Faculty\Council;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CapstoneCouncilGradeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'capstone_id' => $this->capstone_id,
            'student_code' => $this->student->usercode,
            'student_name' => $this->student->full_name,
            'topic_name' => $this->topic->title,
            'defense_order' => $this->defense_order,
            'council_grade' => $this->council_grade,
            'instructor_grade' => $this->instructor_grade,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
