<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->topicData();
    }

    // Đề tài
    private function topicData(): array
    {
        $topicId = $this->topic_id;

        // lecturer
        $lecturer = $this->relationLoaded('lecturer')
            ? [
                'lecturer_id' => $this->lecturer?->lecturer_id,
                'usercode'    => $this->lecturer?->usercode,
                'full_name'   => $this->lecturer?->full_name,
                'expertises'  => $this->lecturer?->relationLoaded('expertises')
                    ? $this->lecturer->expertises->map(fn($e) => [
                        'expertise_id' => $e->expertise_id,
                        'name'         => $e->name,
                    ])
                    : []
            ]
            : null;

        // expertise
        $expertise = $this->relationLoaded('expertise')
            ? [
                'expertise_id' => $this->expertise?->expertise_id,
                'name'         => $this->expertise?->name,
            ]
            : null;

        // faculty staff
        $facultyStaff = $this->relationLoaded('facultyStaff')
            ? [
                'faculty_staff_id' => $this->facultyStaff?->usercode,
                'full_name'        => $this->facultyStaff?->full_name,
            ]
            : null;

        return [
            'topic_id'         => $topicId,
            'lecturer'         => $lecturer,
            'expertise'        => $expertise,
            'expertise_id'     => $this->expertise_id,
            'specialization'   => $this->expertise?->name,
            'faculty_staff'    => $facultyStaff,
            'title'            => $this->title,
            'description'      => $this->description,
            'technologies'     => $this->technologies,
            'is_available'     => $this->is_available,
            'is_bank_topic'    => $this->is_bank_topic
        ];
    }
}
