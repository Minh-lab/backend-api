<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'      => ['required', 'string', 'max:255'],
            'description'=> ['required', 'string'],
            'file'       => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:3072', // 3MB = 3072KB
            ],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date'   => ['required', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Vui lòng không để trống thông tin.',
            'description.required' => 'Vui lòng không để trống thông tin.',
            'file.required'        => 'Vui lòng không để trống thông tin.',
            'file.mimes'           => 'File phải là ảnh/pdf và < 3MB.',
            'file.max'             => 'File phải là ảnh/pdf và < 3MB.',
            'start_date.required'  => 'Vui lòng không để trống thông tin.',
            'start_date.after_or_equal' => 'Ngày bắt đầu không được là ngày trong quá khứ.',
            'end_date.required'    => 'Vui lòng không để trống thông tin.',
            'end_date.after'       => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ];
    }
}