<?php

namespace App\Http\Requests\Topic;

use Illuminate\Foundation\Http\FormRequest;

class TopicRequest extends FormRequest
{
    /**
     * Xác định user có quyền gửi request hay không
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules validate dữ liệu
     */
    public function rules(): array
    {
        return [
            'expertise_id' => 'required|integer|exists:expertises,expertise_id',
            'lecturer_id' => 'nullable|integer|exists:lecturers,lecturer_id',
            'faculty_staff_id' => 'nullable|integer|exists:faculty_staffs,faculty_staff_id',

            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'technologies' => 'required|string|max:255',

            'is_available' => 'nullable|boolean',
            'is_bank_topic' => 'nullable|boolean'
        ];
    }

    /**
     * Thông báo lỗi
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tên đề tài không được để trống.',
            'title.max' => 'Tên đề tài tối đa 255 ký tự.',

            'description.required' => 'Mô tả đề tài không được để trống.',

            'technologies.required' => 'Vui lòng nhập công nghệ sử dụng.',

            'expertise_id.required' => 'Vui lòng chọn chuyên môn.',
            'expertise_id.exists' => 'Chuyên môn không tồn tại.',

            'lecturer_id.exists' => 'Giảng viên không tồn tại.',
            'faculty_staff_id.exists' => 'Cán bộ khoa không tồn tại.'
        ];
    }
}
