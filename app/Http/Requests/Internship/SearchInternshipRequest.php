<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class SearchInternshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword'     => 'nullable|string|max:255', // Tên hoặc MSSV
            'semester_id' => 'nullable|exists:semesters,semester_id',
            'status'      => 'nullable|string',
            'company_id'  => 'nullable|exists:companies,company_id',
            'page'        => 'nullable|integer|min:1',
        ];
    }
}
