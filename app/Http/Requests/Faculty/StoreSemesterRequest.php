<?php

namespace App\Http\Requests\Faculty;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Foundation\Http\FormRequest;

class StoreSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Quyền truy cập kiểm soát bởi middleware role:faculty_staff
    }

    public function rules(): array
    {
        return [
            'year_name'     => ['required', 'string', 'max:100'],
            'semester_name' => ['required', 'string', 'max:100'],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'year_name.required'     => 'Vui lòng không để trống thông tin.',
            'semester_name.required' => 'Vui lòng không để trống thông tin.',
            'start_date.required'    => 'Vui lòng không để trống thông tin.',
            'start_date.date'        => 'Ngày bắt đầu không hợp lệ.',
            'end_date.required'      => 'Vui lòng không để trống thông tin.',
            'end_date.date'          => 'Ngày kết thúc không hợp lệ.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $yearName     = $this->input('year_name');
            $semesterName = $this->input('semester_name');
            $startDate    = $this->input('start_date');
            $endDate      = $this->input('end_date');

            // Rule 1: Ngày bắt đầu < Ngày kết thúc
            if ($startDate >= $endDate) {
                $validator->errors()->add(
                    'end_date',
                    'Ngày bắt đầu phải nhỏ hơn ngày kết thúc.'
                );
                return;
            }

            // Rule 2: Kiểm tra cặp (year_name + semester_name) phải duy nhất
            $existingYear = AcademicYear::where('year_name', $yearName)->first();

            if ($existingYear) {
                $duplicate = Semester::where('year_id', $existingYear->year_id)
                    ->where('semester_name', $semesterName)
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add(
                        'semester_duplicate',
                        'Học kỳ đã tồn tại.'
                    );
                }
            }
        });
    }
}
