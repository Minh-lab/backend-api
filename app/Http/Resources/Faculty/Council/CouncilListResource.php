<?php

namespace App\Http\Resources\Faculty\Council;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouncilListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Dùng cho danh sách councils (lightweight)
     *
     * @return array<string, mixed>
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
            'semester' => $this->semester?->semester_name ?? null,
            'semester_id' => $this->semester_id,
            'student_count' => $this->capstones_count ?? $this->capstones()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
