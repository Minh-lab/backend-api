<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class CancelRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'request_id'      => $this->internship_request_id,
            'internship_id'   => $this->internship_id,
            'type'            => $this->type,
            'status'          => $this->status,
            'created_at'      => $this->created_at->format('Y-m-d H:i:s'),
            'message'         => 'Yêu cầu hủy đang chờ Văn phòng khoa phê duyệt.'
        ];
    }
}
