<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class ApproveCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'             => 'required|in:APPROVED,REJECTED',
            'feedback'           => 'required_if:status,REJECTED|string|nullable', // 6a1: Lý do từ chối
            'student_ids'        => 'required|array|min:1', // BR-4: Danh sách SV được duyệt
            'student_ids.*'      => 'exists:students,student_id',
            // Luồng 5a: Cho phép chỉnh sửa thông tin doanh nghiệp
            'company_name'       => 'sometimes|string',
            'company_address'    => 'sometimes|string',
            'company_email'      => 'sometimes|email',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'      => 'Vui lòng chọn trạng thái phê duyệt.',
            'feedback.required_if' => 'Vui lòng nhập lý do từ chối (6a1).',
            'student_ids.required' => 'Vui lòng chọn danh sách sinh viên (BR-4).',
        ];
    }
}
