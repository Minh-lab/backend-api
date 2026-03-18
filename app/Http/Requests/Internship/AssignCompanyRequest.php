<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class AssignCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id'      => 'required|exists:companies,company_id',
            'internship_ids'  => 'required|array|min:1',
            'internship_ids.*' => 'exists:internships,internship_id',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required'     => 'Vui lòng chọn doanh nghiệp để phân công.',
            'internship_ids.required' => 'Vui lòng chọn ít nhất một sinh viên.',
        ];
    }
}
