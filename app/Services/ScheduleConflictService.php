<?php

namespace App\Services;

use App\Models\Council;
use Carbon\Carbon;

class ScheduleConflictService
{
    /**
     * Check if there is a scheduling conflict with other councils
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $buildings
     * @param string $rooms
     * @param int|null $excludeCouncilId - Council ID to exclude from check
     * @return array - ['has_conflict' => bool, 'conflicting_councils' => array]
     */
    public function hasConflict(
        Carbon $startDate,
        Carbon $endDate,
        string $buildings,
        string $rooms,
        int $excludeCouncilId = null
    ): array {
        $query = Council::where('buildings', $buildings)
            ->where('rooms', $rooms)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date');

        // Exclude current council
        if ($excludeCouncilId) {
            $query->where('council_id', '!=', $excludeCouncilId);
        }

        // Get all councils with same building and room
        $conflictingCouncils = $query->get();

        $conflicts = [];
        foreach ($conflictingCouncils as $council) {
            // Check if time ranges overlap
            // Overlap occurs when: start < other's end AND end > other's start
            if ($startDate->lt($council->end_date) && $endDate->gt($council->start_date)) {
                $conflicts[] = [
                    'council_id' => $council->council_id,
                    'council_name' => $council->name,
                    'start_date' => $council->start_date,
                    'end_date' => $council->end_date,
                ];
            }
        }

        return [
            'has_conflict' => !empty($conflicts),
            'conflicting_councils' => $conflicts,
        ];
    }
}
