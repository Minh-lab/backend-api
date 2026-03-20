<?php

namespace App\Http\Resources\Capstone;

use Illuminate\Http\Resources\Json\JsonResource;

class LecturerSlotResource extends JsonResource
{
    public function toArray($request): array
    {
        $currentSlots = $this->capstones_count ?? 0;
        $maxSlots = 30; // Giả định định mức tối đa của khoa

        return [
            'lecturer_id'   => $this->lecturer_id,
            'full_name'     => $this->full_name,
            'department'    => $this->department,
            'current_slots' => $currentSlots,
            'max_slots'     => $maxSlots,
            'available'     => max(0, $maxSlots - $currentSlots),
            'is_on_leave'   => $this->leaves()->where('lecturer_leaves.status', 'LEAVE_ACTIVE')->exists()
        ];
    }
}
