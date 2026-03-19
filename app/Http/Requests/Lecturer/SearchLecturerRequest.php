<?php

namespace App\Http\Requests\Lecturer;

use Illuminate\Foundation\Http\FormRequest;

class SearchLecturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword'           => 'nullable|string|max:255',
            'expertise_id'      => 'nullable|exists:expertises,expertise_id',
            'slot_status'       => 'nullable|in:available,full', // Còn slot / Hết slot
            'acceptance_status' => 'nullable|in:accepting,busy', // Nhận thêm / Không nhận
        ];
    }
}
