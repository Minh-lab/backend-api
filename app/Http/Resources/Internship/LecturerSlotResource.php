<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\LecturerLeave;

class LecturerSlotResource extends JsonResource
{
    public function toArray($request): array
    {
        // Kiểm tra trạng thái nghỉ phép (BR-1)
        $isOnLeave = $this->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists();

        // Giả sử định mức tối đa là 30 sinh viên/giảng viên (có thể cấu hình trong DB)
        $maxSlots = 30;
        $currentGuiding = $this->internships()->count();

        return [
            'lecturer_id'   => $this->lecturer_id,
            'full_name'     => $this->full_name,
            'usercode'      => $this->usercode,
            'current_slots' => $currentGuiding,
            'max_slots'     => $maxSlots,
            'is_on_leave'   => $isOnLeave,
            'status_label'  => $isOnLeave ? 'Đang nghỉ phép' : 'Đang công tác',
        ];
    }
}
