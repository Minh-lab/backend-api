<?php

namespace App\Http\Requests\Faculty;

use App\Models\Milestone;
use App\Models\Semester;
use Illuminate\Foundation\Http\FormRequest;

class StoreMileStonesRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // milestone_id is auto-increment. Ignore if client accidentally sends it.
        $this->request->remove('milestone_id');
        $this->request->remove('milestoneId');
        $this->request->remove('id');

        // Support route: POST /faculty/semesters/{id}/milestones
        $routeSemesterId = $this->route('id');
        if (!$this->has('semester_id') && $routeSemesterId !== null) {
            $this->merge(['semester_id' => (int) $routeSemesterId]);
        }

        if ($this->has('phase_name')) {
            $this->merge(['phase_name' => trim((string) $this->input('phase_name'))]);
        }
    }

    public function authorize(): bool
    {
        return true; // Quyền truy cập kiểm soát bởi middleware role:faculty_staff
    }

    public function rules(): array
    {
        return [
            'semester_id' => ['required', 'integer', 'exists:semesters,semester_id'],
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
            'semester_id.required' => 'Vui lòng nhập đầy đủ thông tin.',
            'semester_id.exists'   => 'Học kỳ không tồn tại.',
            'phase_name.required'  => 'Vui lòng nhập đầy đủ thông tin.',
            'type.required'        => 'Vui lòng nhập đầy đủ thông tin.',
            'type.in'              => 'Loại mốc phải là Đồ án (CAPSTONE) hoặc Thực tập (INTERNSHIP).',
            'start_date.required'  => 'Vui lòng nhập đầy đủ thông tin.',
            'start_date.date'      => 'Ngày bắt đầu không hợp lệ.',
            'end_date.required'    => 'Vui lòng nhập đầy đủ thông tin.',
            'end_date.date'        => 'Ngày kết thúc không hợp lệ.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Bỏ qua nếu validate cơ bản đã thất bại
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $semesterId = (int) $this->input('semester_id');
            $phaseName  = $this->input('phase_name');
            $type       = $this->input('type');
            $startDate  = $this->input('start_date');
            $endDate    = $this->input('end_date');

            // Rule 0: Không tạo trùng mốc trong cùng kỳ + cùng loại
            $duplicatePhase = Milestone::where('semester_id', $semesterId)
                ->where('type', $type)
                ->whereRaw('LOWER(phase_name) = LOWER(?)', [$phaseName])
                ->exists();

            if ($duplicatePhase) {
                $validator->errors()->add(
                    'phase_name',
                    'Mốc thời gian đã tồn tại trong học kỳ này.'
                );
                return;
            }

            // Rule 1: Ngày bắt đầu < Ngày kết thúc
            if ($startDate >= $endDate) {
                $validator->errors()->add(
                    'end_date',
                    'Thời gian bắt đầu phải nhỏ hơn thời gian kết thúc.'
                );
                return;
            }

            // Rule 2: Lấy học kỳ và kiểm tra mốc nằm trong học kỳ
            $semester = Semester::find($semesterId);
            if ($semester) {
                $semStart = $semester->start_date->format('Y-m-d');
                $semEnd   = $semester->end_date->format('Y-m-d');

                if ($startDate < $semStart || $endDate > $semEnd) {
                    $validator->errors()->add(
                        'start_date',
                        'Mốc thời gian phải nằm trong khoảng thời gian của học kỳ (' . $semStart . ' đến ' . $semEnd . ').'
                    );
                    return;
                }
            }

            // Rule 3: Ngày bắt đầu mốc mới > Ngày kết thúc mốc trước (cùng kỳ, cùng loại)
            $latestMilestone = Milestone::where('semester_id', $semesterId)
                ->where('type', $type)
                ->orderBy('end_date', 'desc')
                ->first();

            if ($latestMilestone) {
                $latestEnd = $latestMilestone->end_date->format('Y-m-d H:i:s');
                if ($startDate <= $latestEnd) {
                    $validator->errors()->add(
                        'start_date',
                        'Ngày bắt đầu phải sau ngày kết thúc của mốc trước (' . $latestEnd . ').'
                    );
                }
            }
        });
    }
}
