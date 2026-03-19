<?php

namespace App\Http\Requests\Capstone;

use Illuminate\Foundation\Http\FormRequest;

class SubmitCapstoneGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // BR-3: Số thập phân từ 0.0 đến 10.0, tối đa 1 chữ số sau dấu chấm
            'grade'    => 'required|numeric|min:0|max:10|regex:/^\d+(\.\d{1})?$/',
            'feedback' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'grade.required' => 'Vui lòng nhập điểm (8a1).',
            'grade.numeric'  => 'Điểm không đúng định dạng, vui lòng nhập lại (8b1).',
            'grade.min'      => 'Điểm phải từ 0.0 trở lên.',
            'grade.max'      => 'Điểm tối đa là 10.0.',
            'grade.regex'    => 'Điểm chỉ được phép có 1 chữ số sau dấu chấm (BR-3).',
        ];
    }
}
