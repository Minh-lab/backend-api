<?php

namespace App\Http\Resources\Faculty\Council;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouncilDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Dùng cho cả tạo (POST) và cập nhật (PUT) council
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'council_id' => $this->council_id,
            'name' => $this->name,
            'semester_id' => $this->semester_id,
            'semester' => $this->semester?->semester_name ?? null,
            'buildings' => $this->buildings,
            'rooms' => $this->rooms,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'members' => CouncilMemberResource::collection($this->members),
            'member_count' => $this->members->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
