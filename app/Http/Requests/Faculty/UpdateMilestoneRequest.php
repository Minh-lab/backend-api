<?php

namespace App\Http\Requests\Faculty;

use App\Models\Milestone;
use App\Models\Semester;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Quyền truy cập kiểm soát bởi middleware role:faculty_staff
    }

    public function rules(): array
    {
        return [
            'phase_name'  => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type'        => ['required', 'in:CAPSTONE,INTERNSHIP'],
            'start_date'  => ['required', 'date'],
            'end_date'    => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'phase_name.required' => 'Vui lòng nhập đầy đủ thông tin.',
            'type.required'       => 'Vui lòng nhập đầy đủ thông tin.',
            'type.in'             => 'Loại mốc phải là Đồ án (CAPSTONE) hoặc Thực tập (INTERNSHIP).',
            'start_date.required' => 'Vui lòng nhập đầy đủ thông tin.',
            'start_date.date'     => 'Ngày bắt đầu không hợp lệ.',
            'end_date.required'   => 'Vui lòng nhập đầy đủ thông tin.',
            'end_date.date'       => 'Ngày kết thúc không hợp lệ.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Bỏ qua nếu validate cơ bản đã thất bại
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $startDate  = $this->input('start_date');
            $endDate    = $this->input('end_date');

            // Rule 1: Ngày bắt đầu < Ngày kết thúc
            if ($startDate >= $endDate) {
                $validator->errors()->add(
                    'end_date',
                    'Thời gian bắt đầu phải nhỏ hơn thời gian kết thúc.'
                );
                return;
            }

            // Lấy milestone hiện tại từ route parameter
            $milestoneId = $this->route('milestone');
            $milestone   = Milestone::find($milestoneId);

            if (!$milestone) {
                $validator->errors()->add('milestone_id', 'Milestone không tồn tại');
                return;
            }

            // Rule 2: Mốc phải nằm trong khoảng thời gian của học kỳ
            $semester = Semester::find($milestone->semester_id);
            if ($semester) {
                $semStart = $semester->start_date->format('Y-m-d');
                $semEnd   = $semester->end_date->format('Y-m-d');

                if ($startDate < $semStart || $endDate > $semEnd) {
                    $validator->errors()->add(
                        'start_date',
                        'Mốc thời gian phải nằm trong khoảng thời gian của học kỳ (' . $semStart . ' đến ' . $semEnd . ').'
                    );
                }
            }
        });
    }
}
