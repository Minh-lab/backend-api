<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class AssignLecturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lecturer_id'     => 'required|exists:lecturers,lecturer_id',
            'internship_ids'  => 'required|array|min:1',
            'internship_ids.*' => 'exists:internships,internship_id',
        ];
    }

    public function messages(): array
    {
        return [
            'lecturer_id.required'    => 'Vui lòng chọn giảng viên hướng dẫn.',
            'internship_ids.required' => 'Vui lòng chọn ít nhất một sinh viên.',
        ];
    }
}
