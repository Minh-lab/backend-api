<?php

namespace App\Http\Resources\Faculty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LecturerListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    return [
            'lecturer_id' => $this->lecturer_id,
            'usercode' => $this->usercode,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'degree' => $this->degree,
            'department' => $this->department,
            'phone_number' => $this->phone_number,
            // Include expertises (specializations)
            'expertises' => $this->expertises->map(function ($expertise) {
                return [
                    'expertise_id' => $expertise->expertise_id,
                    'name' => $expertise->name,
                ];
            })->toArray(),
        ];
    }
}
