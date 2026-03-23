<?php

namespace App\Http\Controllers\Capstone;

use App\Models\{Capstone, Lecturer, Milestone, LecturerLeave};
use App\Http\Requests\Capstone\AssignSupervisorRequest;
use App\Http\Resources\Capstone\LecturerSlotResource;
use Illuminate\Support\Facades\DB;

class SupervisorAssignmentController extends CapstoneBaseController
{
    /**
     * UC 28 - Bước 1: Lấy danh sách đồ án cho VPK quản lý (có phân trang, search, lọc)
     */
    public function getCapstonesList()
    {
        try {
            $page = request()->get('page', 1);
            $itemsPerPage = request()->get('itemsPerPage', 10);
            $search = request()->get('search', '');
            $status = request()->get('status', '');
            $lecturer = request()->get('lecturer', '');
            $council = request()->get('council', '');

            $query = Capstone::with([
                'student.studentClass',
                'topic.expertise',
                'lecturer',
                'council',
                'reviewers.lecturer',
                'requests' => function ($q) {
                    $q->where('type', 'CANCEL_REQ')
                      ->whereIn('status', ['PENDING_TEACHER', 'PENDING_FACULTY', 'APPROVED']);
                }
            ]);

            // Search theo tên sinh viên, mã sinh viên, hoặc đề tài
            if (!empty($search)) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%$search%")
                      ->orWhere('usercode', 'like', "%$search%");
                })->orWhereHas('topic', function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%");
                });
            }

            // Lọc theo trạng thái (không filter nếu không có value hoặc value là "all")
            if (!empty($status) && $status !== 'all') {
                $query->where('status', $status);
            }

            // Lọc theo giảng viên hướng dẫn
            if (!empty($lecturer) && $lecturer !== 'all') {
                $query->where('lecturer_id', $lecturer);
            }

            // Lọc theo hội đồng
            if (!empty($council) && $council !== 'all') {
                $query->where('council_id', $council);
            }

            $total = $query->count();
            $capstones = $query->paginate($itemsPerPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'capstones' => collect($capstones->items())->map(fn($c) => [
                        'capstone_id' => $c->capstone_id,
                        'student' => [
                            'student_id' => $c->student->student_id,
                            'full_name' => $c->student->full_name,
                            'usercode' => $c->student->usercode,
                            'studentClass' => [
                                'class_id' => $c->student->studentClass?->class_id,
                                'class_name' => $c->student->studentClass?->class_name,
                            ]
                        ],
                        'topic' => $c->topic,
                        'lecturer' => $c->lecturer,
                        'council' => $c->council,
                        'status' => $c->status,
                        'instructor_grade' => $c->instructor_grade,
                        'council_grade' => $c->council_grade,
                        'defense_order' => $c->defense_order,
                        'semester_id' => $c->semester_id,
                        'created_at' => $c->created_at,
                        'has_pending_cancel_request' => $c->requests->isNotEmpty(),
                        'pending_cancel_request' => $c->requests->first(),
                        'reviewers' => $c->reviewers,
                    ])->toArray(),
                    'pagination' => [
                        'current_page' => $capstones->currentPage(),
                        'total' => $total,
                        'per_page' => $capstones->perPage(),
                        'last_page' => $capstones->lastPage(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * UC 28 - Lấy chi tiết một đồ án
     */
    public function getCapstoneDetail($id)
    {
        try {
            $capstone = Capstone::with([
                'student.studentClass',
                'topic.expertise',
                'lecturer',
                'council',
                'reviewers.lecturer',
                'requests' => function ($q) {
                    $q->where('type', 'CANCEL_REQ')
                      ->whereIn('status', ['PENDING_TEACHER', 'PENDING_FACULTY', 'APPROVED']);
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'capstone_id' => $capstone->capstone_id,
                    'student' => [
                        'student_id' => $capstone->student->student_id,
                        'full_name' => $capstone->student->full_name,
                        'usercode' => $capstone->student->usercode,
                        'studentClass' => [
                            'class_id' => $capstone->student->studentClass?->class_id,
                            'class_name' => $capstone->student->studentClass?->class_name,
                        ]
                    ],
                    'topic' => $capstone->topic,
                    'lecturer' => $capstone->lecturer,
                    'council' => $capstone->council,
                    'status' => $capstone->status,
                    'instructor_grade' => $capstone->instructor_grade,
                    'council_grade' => $capstone->council_grade,
                    'defense_order' => $capstone->defense_order,
                    'semester_id' => $capstone->semester_id,
                    'created_at' => $capstone->created_at,
                    'has_pending_cancel_request' => $capstone->requests->isNotEmpty(),
                    'pending_cancel_request' => $capstone->requests->first(),
                    'reviewers' => $capstone->reviewers,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đồ án'], 404);
        }
    }

    /**
     * UC 28 - Bước 5: Lấy danh sách giảng viên và số slot để VPK lựa chọn
     */
    public function getLecturerAssignmentList()
    {
        // Lấy giảng viên kèm số lượng đồ án đang hướng dẫn
        $lecturers = Lecturer::withCount('capstones')->get();
        return LecturerSlotResource::collection($lecturers);
    }

    /**
     * UC 28 - Bước 5 (Enhanced): Lấy danh sách giảng viên hướng dẫn với phân trang, search, và chuyên môn
     */
    public function getAdvisorsWithSlots()
    {
        try {
            $page = request()->get('page', 1);
            $itemsPerPage = request()->get('itemsPerPage', 10);
            $search = request()->get('search', '');
            $major = request()->get('major', '');

            $query = Lecturer::with('expertises')
                ->withCount('capstones');

            // Search theo tên hoặc mã giảng viên
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%$search%")
                      ->orWhere('usercode', 'like', "%$search%");
                });
            }

            // Lọc theo chuyên môn
            if ($major && $major !== 'all') {
                $query->whereHas('expertises', function ($q) use ($major) {
                    $q->where('expertise_id', $major);
                });
            }

            $total = $query->count();
            $lecturers = $query->paginate($itemsPerPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'lecturers' => collect($lecturers->items())->map(fn($lecturer) => [
                        'lecturer_id' => $lecturer->lecturer_id,
                        'usercode' => $lecturer->usercode,
                        'full_name' => $lecturer->full_name,
                        'degree' => $lecturer->degree,
                        'department' => $lecturer->department,
                        'expertise' => $lecturer->expertises->pluck('name')->join(', ') ?? 'Không có',
                        'current_slots' => $lecturer->capstones_count ?? 0,
                        'max_slots' => 20,
                        'available_slots' => max(0, 20 - ($lecturer->capstones_count ?? 0)),
                        'is_on_leave' => $lecturer->leaves()->where('lecturer_leaves.status', 'LEAVE_ACTIVE')->exists(),
                    ])->toArray(),
                    'pagination' => [
                        'current_page' => $lecturers->currentPage(),
                        'total' => $total,
                        'per_page' => $lecturers->perPage(),
                        'last_page' => $lecturers->lastPage(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * UC 28 - Bước 7-12: Thực hiện phân công GVHD hàng loạt
     */
    public function assignSupervisor(AssignSupervisorRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $lecturerId = $request->lecturer_id;
            $studentIds = $request->student_ids;
            $lecturer = Lecturer::findOrFail($lecturerId);

            // 4a. Ngoại lệ: Kiểm tra thời gian (Phải sau khi hết hạn đăng ký)
            $milestone = Milestone::capstone()
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            if ($milestone) {
                return response()->json(['message' => 'Đang trong thời gian đăng ký GVHDDA, không thể phân công cưỡng bức (4a1).'], 400);
            }

            // 7d. Ngoại lệ: Giảng viên nghỉ phép (BR-2)
            if ($lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists()) {
                return response()->json(['message' => 'Không thể phân công cho giảng viên đang nghỉ phép (7d1).'], 400);
            }

            // 7b. Ngoại lệ: Kiểm tra slot (BR-1)
            $maxSlots = 20;
            $currentSlots = $lecturer->capstones()->count();
            if (($currentSlots + count($studentIds)) > $maxSlots) {
                return response()->json(['message' => 'Giảng viên không thể tiếp nhận thêm, vui lòng chọn giảng viên khác (7b1).'], 400);
            }

            foreach ($studentIds as $sId) {
                $capstone = Capstone::where('student_id', $sId)->firstOrFail();

                // 7c. Ngoại lệ: Sinh viên đã có GVHD (BR-3)
                if ($capstone->lecturer_id !== null) {
                    return response()->json(['message' => "Sinh viên ID {$sId} đã có giảng viên hướng dẫn (7c1)."], 400);
                }

                // Bước 9: Cập nhật giảng viên
                $capstone->update([
                    'lecturer_id' => $lecturerId,
                    'status'      => Capstone::STATUS_LECTURER_APPROVED
                ]);

                // Gửi thông báo cho sinh viên
                $this->notifyStudent($sId, 1, "Văn phòng khoa đã phân công giảng viên {$lecturer->full_name} hướng dẫn đồ án cho bạn.");
            }

            // Gửi thông báo cho giảng viên
            $this->notifyStudent($lecturerId, 2, "Văn phòng khoa đã phân công thêm " . count($studentIds) . " sinh viên mới vào danh sách hướng dẫn của bạn.");

            return response()->json(['success' => true, 'message' => 'Phân công thành công (Bước 11).']);
        });
    }
}
