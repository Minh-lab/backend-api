<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class RegisterInternshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Quyền đã được kiểm soát ở Middleware Role
    }

    public function rules(): array
    {
        return [
            // Kiểm tra milestone_id có tồn tại và thuộc loại INTERNSHIP không
            'milestone_id' => 'required|exists:milestones,milestone_id',
        ];
    }

    public function messages(): array
    {
        return [
            'milestone_id.required' => 'Thông tin đợt thực tập không được để trống.',
            'milestone_id.exists'   => 'Đợt thực tập không tồn tại trong hệ thống.',
        ];
    }
}
