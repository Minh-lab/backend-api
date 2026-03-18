<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class ReviewCancelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // status: APPROVED (Đồng ý) hoặc REJECTED (Từ chối)
            'status'   => 'required|in:APPROVED,REJECTED',
            'feedback' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn quyết định phê duyệt.',
            'status.in'       => 'Trạng thái phê duyệt không hợp lệ.',
        ];
    }
}
