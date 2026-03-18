<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class EvaluateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // BR-1: 0-10. BR-2: Tối đa 1 chữ số thập phân dùng dấu chấm
            'company_grade'    => ['required', 'numeric', 'min:0', 'max:10', 'regex:/^\d+(\.\d{1})?$/'],
            'company_feedback' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'company_grade.required' => 'Vui lòng nhập điểm (7a1).',
            'company_grade.numeric'  => 'Điểm không đúng định dạng, vui lòng nhập lại (7b1).',
            'company_grade.min'      => 'Điểm không đúng định dạng, vui lòng nhập lại (7b1).',
            'company_grade.max'      => 'Điểm không đúng định dạng, vui lòng nhập lại (7b1).',
            'company_grade.regex'    => 'Điểm chỉ được phép có tối đa 1 chữ số thập phân (BR-2).',
        ];
    }
}
