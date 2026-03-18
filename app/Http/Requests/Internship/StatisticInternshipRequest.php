<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class StatisticInternshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'semester_id' => 'nullable|exists:semesters,semester_id',
            'status'      => 'nullable|string',
            'lecturer_id' => 'nullable|exists:lecturers,lecturer_id',
            'company_id'  => 'nullable|exists:companies,company_id',
        ];
    }
}
