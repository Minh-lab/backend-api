<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LecturerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            // Dữ liệu cơ bản (UC 47 & 48)
            'lecturer_id'   => $this->lecturer_id,
            'usercode'      => $this->usercode,
            'full_name'     => $this->full_name,
            'email'         => $this->email,
            'department'    => $this->department,
            'degree'        => $this->degree,
            'is_active'     => (int) $this->is_active,
            'status_label'  => $this->is_active == 1 ? 'Đang hoạt động' : 'Nghỉ phép',

            // Expertises (Chỉ hiện khi load ở UC 47)
            'expertises'    => $this->whenLoaded('expertises', function () {
                return $this->expertises->pluck('name');
            }),

            // Chi tiết nội dung nghỉ phép (Chỉ hiện ở UC 48 - Bước 4)
            'leave_content' => $this->whenLoaded('requests', function () {
                $leaveReq = $this->requests
                    ->where('type', \App\Models\LecturerRequest::TYPE_LEAVE_REQ)
                    ->where('status', \App\Models\LecturerRequest::STATUS_PENDING)
                    ->first();

                if ($leaveReq) {
                    return [
                        'request_id'  => $leaveReq->request_id,
                        'reason'      => $leaveReq->description, // Dựa trên LecturerRequest.php
                        'start_date'  => $leaveReq->start_date,
                        'end_date'    => $leaveReq->end_date,
                    ];
                }
                return null;
            }),
        ];
    }
}
