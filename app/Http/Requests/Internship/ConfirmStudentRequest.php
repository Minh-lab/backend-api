<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // status: ACCEPT (Đồng ý) hoặc REJECT (Từ chối)
            'status' => 'required|in:ACCEPT,REJECT',
            // feedback: optional, used when rejecting
            'feedback' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn hành động xác nhận.',
            'status.in'       => 'Hành động không hợp lệ (ACCEPT hoặc REJECT).',
        ];
    }
}
