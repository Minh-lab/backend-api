<?php

namespace App\Http\Requests\Faculty;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouncilRequest extends FormRequest
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
            'lecturer_ids' => 'required|array|size:5',
            'lecturer_ids.*' => 'required|integer|exists:lecturers,lecturer_id|distinct',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'lecturer_ids.required' => 'Danh sách giảng viên không được để trống',
            'lecturer_ids.array' => 'Danh sách giảng viên phải là mảng',
            'lecturer_ids.size' => 'Số thành viên hội đồng là 5',
            'lecturer_ids.*.required' => 'ID giảng viên không được để trống',
            'lecturer_ids.*.integer' => 'ID giảng viên phải là số nguyên',
            'lecturer_ids.*.exists' => 'Giảng viên không tồn tại',
            'lecturer_ids.*.distinct' => 'Không thể chọn giảng viên trùng lặp',
        ];
    }
}
