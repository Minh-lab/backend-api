<?php

namespace App\Http\Requests\Capstone;

use Illuminate\Foundation\Http\FormRequest;

class ReviewCancellationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // status: 'APPROVED' (Duyệt) hoặc 'REJECTED' (Từ chối)
            'status'   => 'required|in:APPROVED,REJECTED',
            'feedback' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn quyết định phê duyệt.',
            'status.in'       => 'Hành động không hợp lệ.',
        ];
    }
}
