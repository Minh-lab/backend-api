<?php

namespace App\Services;

use App\Models\Milestone;
use Carbon\Carbon;

class MilestoneService
{
    /**
     * Kiểm tra xem thời gian hiện tại có nằm trong mốc thời gian hay không
     * 
     * @param string $phaseName
     * @param int $semesterId
     * @return array
     */
    public function checkMilestoneStatus(string $phaseName, int $semesterId): array
    {
        // Lấy milestone từ DB
        $milestone = Milestone::where('phase_name', $phaseName)
            ->where('semester_id', $semesterId)
            ->first();

        // Nếu không tìm thấy milestone
        if (!$milestone) {
            return [
                'success' => false,
                'message' => 'Mốc thời gian không tồn tại',
                'data' => [
                    'phase_name' => $phaseName,
                    'semester_id' => $semesterId,
                    'is_active' => false,
                ]
            ];
        }

        // Check thời gian hiện tại
        $now = Carbon::now();
        $startDate = Carbon::parse($milestone->start_date);
        $endDate = Carbon::parse($milestone->end_date);
        
        $isActive = $now->between($startDate, $endDate, true);

        return [
            'success' => true,
            'message' => $isActive 
                ? 'Đang trong thời gian ' . $phaseName
                : 'Không còn thời gian ' . $phaseName,
            'data' => [
                'milestone_id' => $milestone->milestone_id,
                'phase_name' => $milestone->phase_name,
                'description' => $milestone->description,
                'semester_id' => $milestone->semester_id,
                'type' => $milestone->type,
                'start_date' => $milestone->start_date->format('Y-m-d H:i:s'),
                'end_date' => $milestone->end_date->format('Y-m-d H:i:s'),
                'is_active' => $isActive,
                'current_time' => $now->format('Y-m-d H:i:s'),
                'time_remaining' => $isActive ? $endDate->diffForHumans($now) : null,
                'time_passed' => !$isActive && $now->isAfter($endDate),
            ]
        ];
    }

    /**
     * Kiểm tra xem còn thời gian hay không (chỉ return boolean)
     * Dùng cho internal checks trong controller
     * 
     * @param string $phaseName
     * @param int $semesterId
     * @return bool
     */
    public function isMilestoneActive(string $phaseName, int $semesterId): bool
    {
        $milestone = Milestone::where('phase_name', $phaseName)
            ->where('semester_id', $semesterId)
            ->first();

        if (!$milestone) {
            return false;
        }

        $now = Carbon::now();
        $startDate = Carbon::parse($milestone->start_date);
        $endDate = Carbon::parse($milestone->end_date);
        
        return $now->between($startDate, $endDate, true);
    }

    /**
     * Lấy milestone object
     * 
     * @param string $phaseName
     * @param int $semesterId
     * @return Milestone|null
     */
    public function getMilestone(string $phaseName, int $semesterId): ?Milestone
    {
        return Milestone::where('phase_name', $phaseName)
            ->where('semester_id', $semesterId)
            ->first();
    }
}
