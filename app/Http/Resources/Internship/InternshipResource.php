<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Internship;
use App\Models\InternshipRequest; // Added for TYPE_COMPANY_REG
use App\Http\Resources\Internship\InternshipRequestResource; // Explicitly added for clarity

class InternshipResource extends JsonResource
{
    public function toArray($request): array
    {
        // Luôn query trực tiếp từ DB để đảm bảo lấy đúng bản ghi mới nhất và tránh lỗi Collection
        $latest = $this->relationLoaded('requests') 
            ? $this->requests->where('type', InternshipRequest::TYPE_COMPANY_REG)->sortByDesc('created_at')->first()
            : $this->requests()->where('type', InternshipRequest::TYPE_COMPANY_REG)->latest()->first();

        $status = $this->status;
        // Nếu đang ở trạng thái PENDING (đang đăng ký doanh nghiệp) nhưng không tìm thấy yêu cầu nào (có thể do đã bị hủy/xóa)
        // thì trả về trạng thái INITIALIZED để sinh viên có thể thực hiện đăng ký lại.
        if ($status === Internship::STATUS_PENDING && !$latest) {
            $status = Internship::STATUS_INITIALIZED;
        }

        return [
            'internship_id' => $this->internship_id,
            'status'        => $status,
            'semester'      => $this->semester ? [
                'semester_id' => $this->semester->semester_id,
                'name'        => $this->semester->name,
            ] : null,
            'company'       => $this->company ? [
                'name'     => $this->company->name,
                'tax_code' => $this->company->usercode,
                'email'    => $this->company->email,
                'address'  => $this->company->address,
            ] : null,
            'lecturer'      => $this->lecturer ? [
                'full_name' => $this->lecturer->full_name,
            ] : null,
            // Lấy request đăng ký doanh nghiệp mới nhất (loại COMPANY_REG)
            'latest_request' => $latest ? new InternshipRequestResource($latest) : null,
            'position'       => $this->position,
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
