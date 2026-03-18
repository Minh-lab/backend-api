<?php

namespace App\Http\Requests\Faculty;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouncilGradeRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'council_grade' => [
                'required',
                'numeric',
                'min:0',
                'max:10',
                'regex:/^\d+(\.\d{1})?$/',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'council_grade.required' => 'Vui lòng nhập điểm',
            'council_grade.numeric' => 'Điểm không đúng định dạng, vui lòng nhập lại',
            'council_grade.min' => 'Điểm không đúng định dạng, vui lòng nhập lại',
            'council_grade.max' => 'Điểm không đúng định dạng, vui lòng nhập lại',
            'council_grade.regex' => 'Điểm không đúng định dạng, vui lòng nhập lại',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure the council_grade is treated as a string for regex validation
        $this->merge([
            'council_grade' => (string) $this->input('council_grade'),
        ]);
    }
}
