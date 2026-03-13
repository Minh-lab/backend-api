<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
// Import thêm lớp Password để viết code sạch hơn (tùy chọn)
use Illuminate\Validation\Rules\Password;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => [
                'required', 
                'string', 
                // Regex: Ít nhất 8 ký tự (.{8,}), 1 chữ hoa (?=.*[A-Z]), 1 chữ số (?=.*\d)
                'regex:/^(?=.*[A-Z])(?=.*\d).{8,}$/', 
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // Thông báo lỗi khi để trống theo đặc tả
            'email.required' => 'Vui lòng nhập đầy đủ thông tin.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập đầy đủ thông tin.',
            
            // Thông báo lỗi sai định dạng mật khẩu theo đặc tả
            'password.min' => 'Mật khẩu phải bao gồm ít nhất 8 ký tự, 1 chữ hoa, 1 chữ số.',
            'password.regex' => 'Mật khẩu phải bao gồm ít nhất 8 ký tự, 1 chữ hoa, 1 chữ số.',
        ];
    }
}