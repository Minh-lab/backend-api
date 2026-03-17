<?php

namespace App\Http\Requests\Lecturer;

use Illuminate\Foundation\Http\FormRequest;

class SearchLecturerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'keyword'      => 'nullable|string|max:255',
            'department'   => 'nullable|string',
            'expertise_id' => 'nullable|integer',
            'per_page'     => 'nullable|integer|min:1|max:100',
        ];
    }
}
