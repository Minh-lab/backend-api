<?php

namespace App\Http\Requests\Faculty;

use Illuminate\Foundation\Http\FormRequest;

class CheckMilestoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'phase_name' => 'required|string|max:255',
            'semester_id' => 'required|integer|exists:semesters,semester_id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'phase_name.required' => 'Tên giai đoạn không được để trống',
            'phase_name.string' => 'Tên giai đoạn phải là chuỗi ký tự',
            'phase_name.max' => 'Tên giai đoạn không được vượt quá 255 ký tự',
            'semester_id.required' => 'ID học kỳ không được để trống',
            'semester_id.integer' => 'ID học kỳ phải là số nguyên',
            'semester_id.exists' => 'ID học kỳ không tồn tại trong hệ thống',
        ];
    }
}
