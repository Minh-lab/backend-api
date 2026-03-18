<?php

namespace App\Http\Resources\Faculty\Council;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouncilMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'lecturer_id' => $this->pivot?->lecturer_id ?? $this->lecturer_id,
            'name' => $this->full_name,
            'degree' => $this->degree,
            'department' => $this->department,
            'position' => $this->pivot?->position ?? null,
        ];
    }
}
