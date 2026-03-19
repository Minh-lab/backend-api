<?php

namespace App\Http\Resources\Capstone;

use Illuminate\Http\Resources\Json\JsonResource;

class CouncilAssignmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'council_id'   => $this->council_id,
            'council_name' => $this->name,
            'location'     => "Phòng {$this->rooms}, Tòa {$this->buildings}",
            'defense_date' => $this->start_date->format('Y-m-d H:i'),
            // Hiển thị danh sách GV trong hội đồng để VPK chọn (Bước 5)
            'members'      => $this->members->map(function ($lecturer) {
                return [
                    'lecturer_id' => $lecturer->lecturer_id,
                    'full_name'   => $lecturer->full_name,
                    'position'    => $lecturer->pivot->position,
                    // 8c: Kiểm tra trạng thái nghỉ phép
                    'is_on_leave' => $lecturer->leaves()->where('lecturer_leaves.status', 'LEAVE_ACTIVE')->exists(),
                ];
            }),
        ];
    }
}
