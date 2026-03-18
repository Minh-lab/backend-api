<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class RegisterCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'internship_id' => 'required|exists:internships,internship_id',
            'tax_code'      => 'required|string',
            'name'          => 'required|string',
            'address'       => 'required|string',
            'email'         => 'required|email',
            'position'      => 'required|string',
            // BR-2: File <= 3MB, định dạng pdf, png, jpg, jpeg
            'file'          => 'required|file|mimes:pdf,png,jpg,jpeg|max:3072',
        ];
    }

    public function messages(): array
    {
        return [
            'file.max'   => 'Dung lượng file vượt quá 3MB (7a1).',
            'file.mimes' => 'Định dạng không hỗ trợ (chỉ nhận PDF, Ảnh) (7a1).',
            'required'   => 'Vui lòng nhập đầy đủ thông tin (9a1).',
        ];
    }
}
