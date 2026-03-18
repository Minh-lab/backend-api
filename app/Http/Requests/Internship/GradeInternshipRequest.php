<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class GradeInternshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 8a, 8b: Điểm bắt buộc, từ 0 đến 10, định dạng số
            'university_grade' => 'required|numeric|min:0|max:10',
            'feedback'         => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'university_grade.required' => 'Vui lòng nhập điểm (8a1).',
            'university_grade.numeric'  => 'Điểm không đúng định dạng, vui lòng nhập lại (8b1).',
            'university_grade.min'      => 'Điểm không đúng định dạng, vui lòng nhập lại (8b1).',
            'university_grade.max'      => 'Điểm không đúng định dạng, vui lòng nhập lại (8b1).',
        ];
    }
}
