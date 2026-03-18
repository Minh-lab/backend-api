<?php

namespace App\Http\Resources\Faculty\Council;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouncilScheduledResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Dùng khi xếp lịch bảo vệ (PUT schedule)
     */
    public function toArray(Request $request): array
    {
        return [
            'council_id' => $this->council_id,
            'name' => $this->name,
            'buildings' => $this->buildings,
            'rooms' => $this->rooms,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'semester' => [
                'semester_id' => $this->semester->semester_id,
                'name' => $this->semester->name,
            ],
            'members' => CouncilMemberResource::collection($this->members),
            'student_count' => $this->capstones()->count(),
        ];
    }
}
