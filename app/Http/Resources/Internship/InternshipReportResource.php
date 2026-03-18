<?php

namespace App\Http\Resources\Internship;

use Illuminate\Http\Resources\Json\JsonResource;

class InternshipReportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'report_id'       => $this->report_id,
            'phase_name'      => $this->milestone->phase_name ?? 'N/A',
            'status'          => $this->status,
            'description'     => $this->description,
            'file_url'        => $this->file_path ? asset('storage/' . $this->file_path) : null,
            'submission_date' => $this->submission_date->format('Y-m-d H:i:s'),
            'lecturer_feedback' => $this->lecturer_feedback,
        ];
    }
}
