<?php

namespace App\Services;

use App\Models\LecturerLeave;
use Illuminate\Support\Facades\DB;

class LecturerLeaveService
{
    /**
     * Kiểm tra xem giảng viên có đang nghỉ phép hay không
     * 
     * @param int $lecturerId
     * @return bool
     */
    public function isLecturerOnLeave(int $lecturerId): bool
    {
        return LecturerLeave::join('lecturer_requests', 'lecturer_leaves.request_id', '=', 'lecturer_requests.request_id')
            ->where('lecturer_requests.lecturer_id', $lecturerId)
            ->where('lecturer_leaves.status', 'LEAVE_ACTIVE')
            ->exists();
    }

    /**
     * Lọc danh sách giảng viên đang nghỉ phép
     * 
     * @param array $lecturerIds
     * @return array
     */
    public function filterOnLeaveIds(array $lecturerIds): array
    {
        return LecturerLeave::join('lecturer_requests', 'lecturer_leaves.request_id', '=', 'lecturer_requests.request_id')
            ->whereIn('lecturer_requests.lecturer_id', $lecturerIds)
            ->where('lecturer_leaves.status', 'LEAVE_ACTIVE')
            ->distinct()
            ->pluck('lecturer_requests.lecturer_id')
            ->toArray();
    }

    /**
     * Lấy danh sách giảng viên không đang nghỉ phép
     * 
     * @param array $lecturerIds
     * @return array
     */
    public function filterAvailableLecturers(array $lecturerIds): array
    {
        $onLeaveIds = $this->filterOnLeaveIds($lecturerIds);
        return array_diff($lecturerIds, $onLeaveIds);
    }
}

