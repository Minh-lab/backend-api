<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LecturerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'lecturer_id' => $this->lecturer_id,
            'usercode' => $this->usercode,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'phone_number' => $this->phone_number,
            'degree' => $this->degree,
            'department' => $this->department,
            'is_active' => $this->is_active,
            'first_login' => $this->first_login,
            'expertises' => $this->expertises->map(function ($expertise) {
                return [
                    'expertise_id' => $expertise->pivot->expertise_id ?? $expertise->expertise_id,
                    'name' => $expertise->name,
                ];
            }),
            'requests' => $this->requests->map(function ($request) {
                return [
                    'request_id' => $request->request_id,
                    'type' => $request->type,
                    'status' => $request->status,
                    'title' => $request->title,
                    'description' => $request->description,
                    'file_path' => $request->file_path,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'faculty_feedback' => $request->faculty_feedback,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
