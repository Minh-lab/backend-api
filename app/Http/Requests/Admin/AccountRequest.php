<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [];

        // Quy tắc chung cho POST (tạo tài khoản)
        if ($this->isMethod('post')) {
            $role = $this->input('role');

            $rules = [
                'username' => 'required|string|min:3|max:255',
                'email' => 'required|email|max:255',
                'role' => 'required|in:student,lecturer,faculty_staff,admin,company',
                'usercode' => 'sometimes|string|max:255',
            ];

            // Quy tắc cho Student (Sinh viên)
            if ($role === 'student') {
                $rules['student_id'] = 'required|string|max:255';
                $rules['full_name'] = 'required|string|max:255';
                $rules['gender'] = 'required|in:male,female,other';
                $rules['dob'] = 'required|date';
                $rules['phone_number'] = 'required|string|max:255';
                $rules['class_id'] = 'required|integer';
                $rules['gpa'] = 'sometimes|numeric|min:0|max:4';
            }
            // Quy tắc cho Lecturer (Giảng viên)
            elseif ($role === 'lecturer') {
                $rules['lecturer_id'] = 'required|string|max:255';
                $rules['full_name'] = 'required|string|max:255';
                $rules['gender'] = 'required|in:male,female,other';
                $rules['dob'] = 'required|date';
                $rules['degree'] = 'required|string|max:255';
                $rules['phone_number'] = 'required|string|max:255';
                $rules['department'] = 'required|string|max:255';
            }
            // Quy tắc cho Faculty Staff (Văn phòng khoa)
            elseif ($role === 'faculty_staff') {
                $rules['faculty_staff_id'] = 'required|string|max:255';
                $rules['full_name'] = 'required|string|max:255';
                $rules['gender'] = 'required|in:male,female,other';
                $rules['dob'] = 'required|date';
            }
            // Quy tắc cho Admin (Quản trị viên)
            elseif ($role === 'admin') {
                $rules['admin_id'] = 'required|string|max:255';
                $rules['full_name'] = 'required|string|max:255';
                $rules['gender'] = 'required|in:male,female,other';
                $rules['dob'] = 'required|date';
            }
            // Quy tắc cho Company (Doanh nghiệp)
            elseif ($role === 'company') {
                $rules['user_code'] = 'required|string|max:255';
                $rules['name'] = 'required|string|max:255';
                $rules['address'] = 'required|string|max:255';
                $rules['website'] = 'required|string|max:255';
                $rules['is_partnered'] = 'required|in:0,1';
            }
        }

        // Quy tắc cho PUT (sửa tài khoản)
        if ($this->isMethod('put')) {
            $rules = [
                'username' => 'required|string|min:3|max:255',
                'email' => 'required|email|max:255',
                'usercode' => 'sometimes|string|max:255',
                'status' => 'sometimes|in:active,inactive',
                'reset_password' => 'sometimes|boolean',
            ];

            // Quy tắc tùy chọn cho các trường có thể cập nhật
            $role = request()->query('role');

            if ($role === 'student') {
                $rules['full_name'] = 'sometimes|string|max:255';
                $rules['gender'] = 'sometimes|in:male,female,other';
                $rules['dob'] = 'sometimes|date';
                $rules['phone_number'] = 'sometimes|string|max:255';
                $rules['class_id'] = 'sometimes|integer';
                $rules['gpa'] = 'sometimes|numeric|min:0|max:4';
            } elseif ($role === 'lecturer') {
                $rules['full_name'] = 'sometimes|string|max:255';
                $rules['gender'] = 'sometimes|in:male,female,other';
                $rules['dob'] = 'sometimes|date';
                $rules['degree'] = 'sometimes|string|max:255';
                $rules['phone_number'] = 'sometimes|string|max:255';
                $rules['department'] = 'sometimes|string|max:255';
            } elseif ($role === 'faculty_staff') {
                $rules['full_name'] = 'sometimes|string|max:255';
                $rules['gender'] = 'sometimes|in:male,female,other';
                $rules['dob'] = 'sometimes|date';
            } elseif ($role === 'admin') {
                $rules['full_name'] = 'sometimes|string|max:255';
                $rules['gender'] = 'sometimes|in:male,female,other';
                $rules['dob'] = 'sometimes|date';
            } elseif ($role === 'company') {
                $rules['name'] = 'sometimes|string|max:255';
                $rules['address'] = 'sometimes|string|max:255';
                $rules['website'] = 'sometimes|string|max:255';
                $rules['is_partnered'] = 'sometimes|in:0,1';
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Tên tài khoản không được để trống.',
            'username.min' => 'Tên tài khoản phải có ít nhất 3 ký tự.',
            'username.max' => 'Tên tài khoản không được vượt quá 255 ký tự.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không hợp lệ.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'role.required' => 'Vai trò không được để trống.',
            'role.in' => 'Vai trò không hợp lệ.',
            'usercode.max' => 'Mã không được vượt quá 255 ký tự.',
            
            // Student fields
            'student_id.required' => 'Mã sinh viên không được để trống.',
            'student_id.max' => 'Mã sinh viên không được vượt quá 255 ký tự.',
            'class_id.required' => 'Lớp không được để trống.',
            'class_id.integer' => 'Lớp phải là một số nguyên.',
            'gpa.numeric' => 'GPA phải là một số.',
            'gpa.min' => 'GPA không được nhỏ hơn 0.',
            'gpa.max' => 'GPA không được lớn hơn 4.',
            
            // Lecturer fields
            'lecturer_id.required' => 'Mã giảng viên không được để trống.',
            'lecturer_id.max' => 'Mã giảng viên không được vượt quá 255 ký tự.',
            'degree.required' => 'Học hàm/Học vị không được để trống.',
            'degree.max' => 'Học hàm/Học vị không được vượt quá 255 ký tự.',
            'department.required' => 'Khoa không được để trống.',
            'department.max' => 'Khoa không được vượt quá 255 ký tự.',
            
            // Faculty Staff fields
            'faculty_staff_id.required' => 'Mã nhân viên không được để trống.',
            'faculty_staff_id.max' => 'Mã nhân viên không được vượt quá 255 ký tự.',
            
            // Admin fields
            'admin_id.required' => 'Mã quản trị viên không được để trống.',
            'admin_id.max' => 'Mã quản trị viên không được vượt quá 255 ký tự.',
            
            // Company fields
            'user_code.required' => 'Mã số thuế không được để trống.',
            'user_code.max' => 'Mã số thuế không được vượt quá 255 ký tự.',
            'name.required' => 'Tên doanh nghiệp không được để trống.',
            'name.max' => 'Tên doanh nghiệp không được vượt quá 255 ký tự.',
            'address.required' => 'Địa chỉ trụ sở không được để trống.',
            'address.max' => 'Địa chỉ trụ sở không được vượt quá 255 ký tự.',
            'website.required' => 'Địa chỉ website không được để trống.',
            'website.max' => 'Địa chỉ website không được vượt quá 255 ký tự.',
            'is_partnered.required' => 'Trạng thái đối tác không được để trống.',
            'is_partnered.in' => 'Trạng thái đối tác phải là 0 hoặc 1.',
            
            // Common fields
            'full_name.required' => 'Họ tên không được để trống.',
            'full_name.max' => 'Họ tên không được vượt quá 255 ký tự.',
            'gender.required' => 'Giới tính không được để trống.',
            'gender.in' => 'Giới tính phải là male, female hoặc other.',
            'dob.required' => 'Ngày sinh không được để trống.',
            'dob.date' => 'Ngày sinh phải ở định dạng ngày hợp lệ.',
            'phone_number.required' => 'Số điện thoại không được để trống.',
            'phone_number.max' => 'Số điện thoại không được vượt quá 255 ký tự.',
            
            'status.in' => 'Trạng thái không hợp lệ.',
            'reset_password.boolean' => 'Reset mật khẩu phải là true hoặc false.',
        ];
    }
}
