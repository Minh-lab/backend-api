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
            'is_active' => (bool) $this->is_active,
            // Include expertises (specializations)
            'expertises' => $this->expertises->map(function ($expertise) {
                return [
                    'expertise_id' => $expertise->expertise_id,
                    'name' => $expertise->name,
                ];
            })->toArray(),
            // Include leaves (for status detection)
            'leaves' => $this->leaves->map(function ($leave) {
                return [
                    'leave_id' => $leave->leave_id,
                    'status' => $leave->status,
                ];
            })->toArray(),
            // Include requests (for status detection)
            'requests' => $this->requests->map(function ($request) {
                return [
                    'request_id' => $request->request_id,
                    'type' => $request->type,
                    'status' => $request->status,
                ];
            })->toArray(),
        ];
    }
}
