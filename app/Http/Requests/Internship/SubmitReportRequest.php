<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'milestone_id' => 'required|exists:milestones,milestone_id',
            'description'  => 'nullable|string|max:500',
            // BR-3: Chỉ PDF, tối đa 10MB (10240 KB)
            'file'         => 'required|file|mimes:pdf|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn tệp tin báo cáo (6a1).',
            'file.mimes'    => 'File phải có định dạng PDF (7a1).',
            'file.max'      => 'Dung lượng file không quá 10MB (7a2).',
            'milestone_id.required' => 'Vui lòng chọn loại báo cáo cần nộp (6a1).',
        ];
    }
}
