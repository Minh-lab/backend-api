<?php

namespace App\Http\Requests\Faculty;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleDefenseRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_date' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_date'],
            'buildings' => ['required', 'string', 'max:255'],
            'rooms' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'start_date.required' => 'Thời gian bắt đầu là bắt buộc',
            'start_date.date_format' => 'Thời gian bắt đầu phải có định dạng Y-m-d H:i:s',
            'end_date.required' => 'Thời gian kết thúc là bắt buộc',
            'end_date.date_format' => 'Thời gian kết thúc phải có định dạng Y-m-d H:i:s',
            'end_date.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu',
            'buildings.required' => 'Tòa là bắt buộc',
            'buildings.string' => 'Tòa phải là chuỗi ký tự',
            'buildings.max' => 'Tòa không được vượt quá 255 ký tự',
            'rooms.required' => 'Phòng là bắt buộc',
            'rooms.string' => 'Phòng phải là chuỗi ký tự',
            'rooms.max' => 'Phòng không được vượt quá 255 ký tự',
        ];
    }
}
