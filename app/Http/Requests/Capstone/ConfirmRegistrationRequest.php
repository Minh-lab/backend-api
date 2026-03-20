<?php

namespace App\Http\Requests\Capstone;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // action: 'APPROVE' hoặc 'REJECT'
            'action'   => 'required|in:APPROVE,REJECT',
            'feedback' => 'nullable|string|max:1000', // Nhận xét thêm nếu từ chối
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Vui lòng chọn hành động Phê duyệt hoặc Từ chối.',
            'action.in'       => 'Hành động không hợp lệ.',
        ];
    }
}
