<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyApprovalResource extends JsonResource
{
    public function toArray($request)
    {
        // Phân biệt DN chính thức và DN đề xuất
        $companyData = $this->company ?? $this->proposedCompany;

        return [
            'request_id'   => $this->internship_request_id,
            'tax_code'     => $companyData->usercode ?? $companyData->tax_code,
            'company_name' => $companyData->name,
            'address'      => $companyData->address,
            'email'        => $companyData->email ?? $companyData->contact_email,
            'status'       => $this->status,
            'file_proof'   => $this->file_path ? asset('storage/' . $this->file_path) : null,
            // Thông tin sinh viên đăng ký (Bước 2)
            'student_count' => $this->where('company_id', $this->company_id)
                ->where('proposed_company_id', $this->proposed_company_id)
                ->count(),
            'students' => $this->internship->student ? [[
                'id' => $this->internship->student->student_id,
                'name' => $this->internship->student->full_name,
                'usercode' => $this->internship->student->usercode
            ]] : []
        ];
    }
}
