<?php

namespace App\Http\Requests\Capstone;

use Illuminate\Foundation\Http\FormRequest;

class ApproveCapstoneReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // status: APPROVED (Phê duyệt) hoặc REJECTED (Từ chối)
            'status'   => 'required|in:APPROVED,REJECTED',
            'feedback' => 'nullable|string|max:2000', // Nhận xét (Luồng 6a)
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn quyết định phê duyệt.',
            'status.in'       => 'Trạng thái không hợp lệ.',
        ];
    }
}
