<?php

namespace App\Http\Requests\Capstone;

use Illuminate\Foundation\Http\FormRequest;

class AssignSupervisorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:capstones,student_id',
            'lecturer_id'   => 'required|exists:lecturers,lecturer_id',
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.required' => 'Vui lòng chọn ít nhất một sinh viên.',
            'lecturer_id.required' => 'Vui lòng chọn giảng viên để phân công.',
        ];
    }
}
