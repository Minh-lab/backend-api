<?php

namespace App\Http\Resources;

use App\Models\Capstone;
use App\Models\Internship;
use App\Models\LecturerLeave;
use App\Models\FacultyStaff;
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
            'faculty_staff' => $this->facultyStaffData(),
            'admin' => $this->adminData(),
            'company' => $this->companyData(),
            default => [],
        };
    }


    // SINH VIÊN
    // Hiển thị: mã SV, họ tên, giới tính, ngày sinh, lớp, email, GPA

    private function studentData(): array
    
    {
        
        return [
            'student_id' => $this->student_id,
            'usercode' => $this->usercode,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'class' => $this->relationLoaded('studentClass')
                ? ($this->studentClass?->class_name ?? null)
                : null,
            'email' => $this->email,
            'gpa' => $this->gpa,
            
        ];
    }


    // GIẢNG VIÊN
    // Hiển thị: mã GV, họ tên, giới tính, ngày sinh, học hàm/học vị,
    //           email, sđt, chuyên môn, bộ môn,
    //           trạng thái (ACTIVE / ON_LEAVE / MAX_LOAD),
    //           số SV đang hướng dẫn đồ án + thực tập

    private function lecturerData(): array
    {
        $lecturerId = $this->lecturer_id;

        $expertises = $this->relationLoaded('expertises')
            ? $this->expertises
                ->map(fn($expertise) => [
                    'expertise_id' => $expertise->expertise_id,
                    'name' => $expertise->name,
                ])
                ->filter(fn($e) => $e['name'] !== null)
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
            'lecturer_id' => $lecturerId,
            'usercode' => $this->usercode,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'degree' => $this->degree,         // Học hàm/học vị
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'expertises' => $expertises,           // Chuyên môn
            'department' => $this->department,     // Bộ môn
            'status' => $this->getLecturerStatus($lecturerId), // ACTIVE | ON_LEAVE | MAX_LOAD
            'capstone_count' => $capstoneCount,        // Số SV đồ án đang hướng dẫn
            'internship_count' => $internshipCount,      // Số SV thực tập đang hướng dẫn
        ];
    }


    // VĂN PHÒNG KHOA
    // Hiển thị: mã nhân viên, họ tên, email, giới tính, ngày sinh

    private function facultyStaffData(): array
    {
        return [
            'faculty_staff_id' => $this->faculty_staff_id,
            'usercode' => $this->usercode,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'gender' => $this->gender,
            'dob' => $this->dob,
        ];
    }


    // ADMIN
    // Hiển thị: mã nhân viên, họ tên, email, giới tính, ngày sinh

    private function adminData(): array
    {
        return [
            'admin_id' => $this->admin_id,
            'usercode' => $this->usercode,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'gender' => $this->gender ?? null,
            'dob' => $this->dob ?? null,
        ];
    }


    // DOANH NGHIỆP
    // Hiển thị: tên, email, địa chỉ, website

    private function companyData(): array
    {
        return [
            'company_id' => $this->company_id,
            'usercode' => $this->usercode,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'website' => $this->website,
        ];
    }


    // HELPER: Trạng thái giảng viên
    // ACTIVE   → đang hoạt động bình thường
    // ON_LEAVE → đang trong thời gian nghỉ phép được duyệt
    // MAX_LOAD → đã đạt giới hạn hướng dẫn (5 đồ án + 5 thực tập)

    private function getLecturerStatus(int $lecturerId): string
    {
        // Ưu tiên 1: đang nghỉ phép – tìm qua bảng request vì lecturer_leaves không
        // chứa cột lecturer_id, chỉ có khóa ngoại request_id.
        $isOnLeave = LecturerLeave::whereHas('request', function ($q) use ($lecturerId) {
                $q->where('lecturer_id', $lecturerId)
                  ->where('status', 'APPROVED');
            })
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->exists();

        if ($isOnLeave) {
            return 'ON_LEAVE';
        }

        // Ưu tiên 2: đã đạt giới hạn hướng dẫn
        $capstoneCount = Capstone::where('lecturer_id', $lecturerId)
            ->whereNotIn('status', ['COMPLETED', 'FAILED', 'CANCEL'])
            ->count();

        $internshipCount = Internship::where('lecturer_id', $lecturerId)
            ->whereNotIn('status', ['COMPLETED', 'FAILED', 'CANCEL'])
            ->count();

        // Giới hạn: 5 đồ án + 5 thực tập (điều chỉnh theo quy định thực tế)
        if ($capstoneCount >= 5 && $internshipCount >= 5) {
            return 'MAX_LOAD';
        }

        return 'ACTIVE';
    }

    // ════════════════════════════════════════
    // HELPER: Trạng thái sinh viên
    // Trả về trạng thái đồ án + thực tập đang hoạt động
    // ════════════════════════════════════════
    // private function getStudentStatuses(int $studentId): array
    // {
    //     $capstone = Capstone::where('student_id', $studentId)
    //         ->whereNotIn('status', ['COMPLETED', 'FAILED', 'CANCEL'])
    //         ->latest('created_at')
    //         ->first();

    //     $internship = Internship::where('student_id', $studentId)
    //         ->whereNotIn('status', ['COMPLETED', 'FAILED', 'CANCEL'])
    //         ->latest('created_at')
    //         ->first();

    //     return [
    //         'capstone' => $capstone ? [
    //             'capstone_id' => $capstone->capstone_id,
    //             'status'      => $capstone->status,
    //         ] : null,

    //         'internship' => $internship ? [
    //             'internship_id' => $internship->internship_id,
    //             'status'        => $internship->status,
    //         ] : null,
    //     ];
    // }
}