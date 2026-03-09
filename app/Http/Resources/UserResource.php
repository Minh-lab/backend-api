<?php

namespace App\Http\Resources;

use App\Models\Capstone;
use App\Models\Internship;
use App\Models\LecturerLeave;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    protected string $role;

    public function __construct($resource, string $role = '')
    {
        parent::__construct($resource);
        $this->role = $role;
    }

    public function toArray($request): array
    {
        return match ($this->role) {
            'student' => $this->studentData(),
            'lecturer' => $this->lecturerData(),
            'faculty-staff' => $this->facultyStaffData(),
            'admin' => $this->adminData(),
            'company' => $this->companyData(),
            default => [],
        };
    }

    // ─── SINH VIÊN ───────────────────────────────────────────
    private function studentData(): array
    {
        return [
            'student_id' => $this->student_id,
            'usercode' => $this->usercode,    // ← mã hiển thị
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'class' => $this->relationLoaded('class')
                ? ($this->class?->class_name ?? null)
                : null,
            'email' => $this->email,
            'gpa' => $this->gpa,
            'statuses' => $this->getStudentStatuses($this->student_id),
        ];
    }

    // ─── GIẢNG VIÊN ──────────────────────────────────────────
    private function lecturerData(): array
    {
        $lecturerId = $this->lecturer_id;

        $expertises = $this->relationLoaded('lecturerExpertises')
            ? $this->lecturerExpertises
                ->map(fn($le) => $le->expertise?->name)
                ->filter()
                ->values()
                ->toArray()
            : [];

        $capstoneCount = Capstone::where('lecturer_id', $lecturerId)
            ->whereNotIn('status', ['COMPLETED', 'FAILED', 'CANCEL'])
            ->count();

        $internshipCount = Internship::where('lecturer_id', $lecturerId)
            ->whereNotIn('status', ['COMPLETED', 'FAILED', 'CANCEL'])
            ->count();

        return [
            'lecturer_id' =>  $lecturerId,
            'usercode' => $this->usercode,    // ← mã hiển thị
            'username' => $this->username,
            'email' => $this->email,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'phone_number' => $this->phone_number,
            'degree' => $this->degree,
            'expertises' => $expertises,
            'department' => $this->department,
            'status' => $this->getLecturerStatus($lecturerId),
            'capstone_count' => $capstoneCount,
            'internship_count' => $internshipCount,
        ];
    }

    // ─── VĂN PHÒNG KHOA ──────────────────────────────────────
    private function facultyStaffData(): array
    {
        return [
            'faculty_staff_id'=> $this->faculty_staff_id,
            'usercode' => $this->usercode,    // ← mã hiển thị,
            'username' => $this->username,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'phone_number' => $this->phone_number
        ];
    }

    // ─── ADMIN ───────────────────────────────────────────────
    private function adminData(): array
    {
        return [
            'admin_id' => $this->admin_id,
            'usercode' => $this->usercode,    // ← mã hiển thị
            'username' => $this->username,    // ← mã hiển thị
            'full_name' => $this->full_name,
            'email' => $this->email,
            'gender' => $this->gender ?? null,
            'dob' => $this->dob ?? null,
        ];
    }

    // ─── DOANH NGHIỆP ────────────────────────────────────────
    private function companyData(): array
    {
        return [
            'company_id' => $this->company_id,    // ← mã hiển thị
            'username' => $this->username,    // ← mã hiển thị
            'usercode' => $this->usercode,    // ← mã hiển thị
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'website' => $this->website,
        ];
    }
}