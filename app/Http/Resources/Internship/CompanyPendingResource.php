<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyPendingResource extends JsonResource
{
    public function toArray($request): array
    {
        // Phân biệt thông tin từ DN chính thức hoặc DN đề xuất
        $company = $this->company ?? $this->proposedCompany;

        return [
            'request_id'   => $this->internship_request_id,
            'tax_code'     => $company->usercode ?? $company->tax_code,
            'name'         => $company->name,
            'address'      => $company->address,
            'email'        => $company->email ?? $company->contact_email,
            'status'       => $this->status,
            'file_path'    => $this->file_path ? asset('storage/' . $this->file_path) : null,
            // Bước 2: Số lượng sinh viên đăng ký vào doanh nghiệp này
            'student_info' => [
                'student_id' => $this->internship->student->student_id ?? null,
                'full_name'  => $this->internship->student->full_name ?? 'N/A',
                'usercode'   => $this->internship->student->usercode ?? 'N/A',
            ]
        ];
    }
}
