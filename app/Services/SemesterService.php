<?php

namespace App\Services;

use App\Models\Semester;
use Carbon\Carbon;

class SemesterService
{
    /**
     * Lấy semester hiện tại (hôm nay nằm trong start_date - end_date)
     * 
     * @return Semester|null
     */
    public function getCurrentSemester(): ?Semester
    {
        $now = Carbon::now();
        
        return Semester::whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->first();
    }

    /**
     * Lấy semester theo ID
     * 
     * @param int $semesterId
     * @return Semester|null
     */
    public function getSemester(int $semesterId): ?Semester
    {
        return Semester::find($semesterId);
    }
}
