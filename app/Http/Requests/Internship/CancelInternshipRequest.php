<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class CancelInternshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'internship_id' => 'required|exists:internships,internship_id',
        ];
    }

    public function messages(): array
    {
        return [
            'internship_id.required' => 'Không tìm thấy thông tin học phần thực tập.',
            'internship_id.exists'   => 'Học phần thực tập không tồn tại.',
        ];
    }
}
