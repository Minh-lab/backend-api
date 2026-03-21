<?php

namespace App\Http\Resources\Lecturer;

use Illuminate\Http\Resources\Json\JsonResource;

class LecturerSearchResource extends JsonResource
{
    public function toArray($request): array
    {
        // Tính toán số lượng sinh viên đang hướng dẫn (Internship + Capstone)
        $currentInterns = $this->internships_count ?? 0;
        $currentCapstones = $this->capstones_count ?? 0;
        $totalGuided = $currentInterns + $currentCapstones;

        $maxSlots = 30; // Định mức giả định

        // Trạng thái tiếp nhận dựa trên việc có đang nghỉ phép hay không
        $isOnLeave = $this->leaves()->where('lecturer_leaves.status', 'LEAVE_ACTIVE')->exists();

        return [
            'lecturer_id'   => $this->lecturer_id,
            'full_name'     => $this->full_name,
            'email'         => $this->email,
            'department'    => $this->department,
            'expertises'    => $this->expertises->pluck('name'),
            'slots' => [
                'current'   => $totalGuided,
                'max'       => $maxSlots,
                'is_full'   => $totalGuided >= $maxSlots
            ],
            'is_accepting'  => !$isOnLeave && ($totalGuided < $maxSlots),
            'status_label'  => $isOnLeave ? 'Đang nghỉ phép' : ($totalGuided >= $maxSlots ? 'Đã hết slot' : 'Đang nhận sinh viên')
        ];
    }
}
