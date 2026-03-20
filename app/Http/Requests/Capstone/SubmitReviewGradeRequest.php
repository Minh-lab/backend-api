<?php

namespace App\Http\Requests\Capstone;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // BR-3: Số thập phân 0.0 - 10.0, 1 chữ số sau dấu chấm
            'grade'    => 'required|numeric|min:0|max:10|regex:/^\d+(\.\d{1})?$/',
            'feedback' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'grade.required' => 'Vui lòng nhập điểm phản biện (8a1).',
            'grade.numeric'  => 'Điểm không đúng định dạng, vui lòng nhập lại (8b1).',
            'grade.regex'    => 'Điểm chỉ được phép có tối đa 1 chữ số sau dấu chấm.',
        ];
    }
}
