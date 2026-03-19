<?php

namespace App\Http\Requests\Capstone;

use Illuminate\Foundation\Http\FormRequest;

class AssignCouncilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_ids'    => 'required|array|min:1',
            'student_ids.*'  => 'exists:capstones,student_id',
            'council_id'     => 'required|exists:councils,council_id',
            // BR-2: Bắt buộc chọn đúng 2 giảng viên phản biện
            'reviewer_ids'   => 'required|array|size:2',
            'reviewer_ids.*' => 'exists:lecturers,lecturer_id',
        ];
    }

    public function messages(): array
    {
        return [
            'reviewer_ids.size' => 'Vui lòng chọn chính xác 2 giảng viên phản biện (8d1).',
            'student_ids.required' => 'Chưa chọn sinh viên để phân công.',
        ];
    }
}
