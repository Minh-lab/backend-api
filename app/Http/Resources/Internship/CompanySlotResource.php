<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanySlotResource extends JsonResource
{
    public function toArray($request): array
    {
        // BR-2: Giới hạn tối đa 20 sinh viên
        $maxSlots = 20;
        $currentInterns = $this->internships()->count();

        return [
            'company_id'   => $this->company_id,
            'tax_code'     => $this->usercode,
            'name'         => $this->name,
            'current_slots' => $currentInterns,
            'max_slots'    => $maxSlots,
            'available'    => $maxSlots - $currentInterns,
        ];
    }
}
