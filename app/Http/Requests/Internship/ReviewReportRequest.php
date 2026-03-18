<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class ReviewReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'   => 'required|in:APPROVED,REJECTED',
            'feedback' => 'required|string|max:1000', // Nội dung nhận xét (Bước 6)
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'   => 'Vui lòng chọn trạng thái Duyệt hoặc Từ chối.',
            'feedback.required' => 'Vui lòng nhập nội dung nhận xét cho sinh viên.',
        ];
    }
}
