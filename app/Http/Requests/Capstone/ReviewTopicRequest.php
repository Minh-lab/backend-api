<?php

namespace App\Http\Requests\Capstone;

use Illuminate\Foundation\Http\FormRequest;

class ReviewTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'   => 'required|in:APPROVED,REJECTED', // APPROVED tương ứng với Duyệt/Đồng ý
            'feedback' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái phê duyệt.',
            'status.in'       => 'Trạng thái không hợp lệ.',
        ];
    }
}
