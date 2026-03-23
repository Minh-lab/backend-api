<?php

namespace App\Http\Controllers\Internship;

use App\Models\{Internship, InternshipRequest, Company, Notification, UserNotification};
use App\Http\Requests\Internship\ConfirmStudentRequest;
use App\Http\Resources\Internship\InternshipResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BusinessInternshipController extends InternshipBaseController
{
    /**
     * UC 45 - Bước 2: Danh sách sinh viên chờ xác nhận từ doanh nghiệp
     * Lấy danh sách InternshipRequest với status = PENDING_COMPANY và type = COMPANY_REG
     */
    public function getWaitingStudents()
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? $user->getAuthIdentifier();
        
        // Lấy danh sách yêu cầu công ty chờ doanh nghiệp duyệt
        $requests = InternshipRequest::where('company_id', $companyId)
            ->where('status', InternshipRequest::STATUS_PENDING_COMPANY)
            ->where('type', InternshipRequest::TYPE_COMPANY_REG)
            ->with([
                'internship.student.studentClass',
                'internship.lecturer',
                'company'
            ])
            ->get();

        // Transform để trả về dữ liệu phù hợp cho frontend
        $result = $requests->map(function($request) {
            $internship = $request->internship;
            return [
                'internship_request_id' => $request->internship_request_id,
                'internship_id' => $internship->internship_id,
                'student_code' => $internship->student->usercode ?? 'N/A',
                'full_name' => $internship->student->full_name,
                'student_class' => $internship->student->studentClass->class_name ?? 'N/A',
                'company_id' => $request->company_id,
                'company_name' => $request->company->name ?? 'N/A',
                'lecturer_name' => $internship->lecturer->full_name ?? 'Chưa phân công',
                'position' => $internship->position,
                'student_message' => $request->student_message,
                'created_at' => $request->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Danh sách sinh viên chờ xác nhận',
            'data' => $result
        ]);
    }

    /**
     * UC 45 - Bước 4: Xác nhận/Từ chối sinh viên
     * Cập nhật InternshipRequest và Internship status
     */
    public function confirmStudent(ConfirmStudentRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = auth()->user();
            $companyId = $user->company_id ?? $user->getAuthIdentifier();

            // Tìm InternshipRequest theo internship_id hoặc internship_request_id
            $internshipRequest = InternshipRequest::where(function($q) use ($id) {
                $q->where('internship_request_id', $id)
                  ->orWhere('internship_id', $id);
            })
            ->where('status', InternshipRequest::STATUS_PENDING_COMPANY)
            ->where('type', InternshipRequest::TYPE_COMPANY_REG)
            ->first();

            if (!$internshipRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu hoặc yêu cầu không ở trạng thái chờ duyệt.'
                ], 404);
            }

            // Kiểm tra quyền: chỉ doanh nghiệp được chỉ định mới có quyền xác nhận
            if ($internshipRequest->company_id != $companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xác nhận yêu cầu này.'
                ], 403);
            }

            $status = $request->status; // 'ACCEPT' hoặc 'REJECT' từ ConfirmStudentRequest

            if ($status === 'ACCEPT') {
                // Cập nhật InternshipRequest
                $internshipRequest->status = InternshipRequest::STATUS_APPROVED;
                $internshipRequest->save();

                // Cập nhật Internship
                $internship = Internship::findOrFail($internshipRequest->internship_id);
                $internship->company_id = $internshipRequest->company_id;
                $internship->status = Internship::STATUS_COMPANY_APPROVED;
                $internship->save();

                $message = 'Sinh viên đã được doanh nghiệp chấp nhận.';
            } elseif ($status === 'REJECT') {
                // Cập nhật InternshipRequest
                $internshipRequest->status = InternshipRequest::STATUS_REJECTED;
                $internshipRequest->feedback = $request->feedback ?? '';
                $internshipRequest->save();

                // Cập nhật Internship
                $internship = Internship::findOrFail($internshipRequest->internship_id);
                $internship->status = Internship::STATUS_FAILED;
                $internship->company_feedback = $request->feedback ?? '';
                $internship->save();

                $message = 'Sinh viên đã bị từ chối bởi doanh nghiệp.';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Hành động không hợp lệ.'
                ], 400);
            }

            // Gửi thông báo cho sinh viên
            $notification = Notification::create([
                'title' => 'Thông báo xác nhận thực tập',
                'content' => $message
            ]);

            UserNotification::create([
                'notification_id' => $notification->notification_id,
                'user_id' => $internship->student_id,
                'role_id' => 1, // Student role
                'is_read' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Xác nhận thành công.',
                'data' => new InternshipResource($internship)
            ]);
        });
    }

    /**
     * UC 46 - Bước 2: Danh sách sinh viên đang thực tập
     */
    public function getInterns()
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? $user->getAuthIdentifier();
        
        // Lấy danh sách sinh viên đang thực tập tại doanh nghiệp
        $internships = Internship::where('company_id', $companyId)
            ->whereIn('status', [
                Internship::STATUS_INTERNING,
                Internship::STATUS_COMPLETED
            ])
            ->with(['student.studentClass', 'company', 'lecturer', 'reports'])
            ->get();

        // Use CompanyInternshipResource to return student code, full name, class name, etc.
        return response()->json([
            'success' => true,
            'data' => \App\Http\Resources\Internship\CompanyInternshipResource::collection($internships)
        ]);
    }

    /**
     * UC 46 - Bước 6: Gửi đánh giá và điểm số từ doanh nghiệp
     */
    public function evaluateStudent(\Illuminate\Http\Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = auth()->user();
            $companyId = $user->company_id ?? $user->getAuthIdentifier();

            $internship = Internship::findOrFail($id);

            // Kiểm tra quyền
            if ($internship->company_id != $companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền đánh giá sinh viên này.'
                ], 403);
            }

            // Kiểm tra trạng thái
            if (!in_array($internship->status, [Internship::STATUS_INTERNING, Internship::STATUS_COMPLETED])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sinh viên này không ở trạng thái đang thực tập.'
                ], 400);
            }

            // Cập nhật đánh giá của doanh nghiệp
            $internship->company_feedback = $request->input('company_feedback') ?? $request->input('feedback') ?? null;
            $internship->company_grade = $request->input('company_grade') ?? $request->input('grade') ?? null;
            $internship->updated_at = Carbon::now();
            $internship->save();

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá đã được lưu.',
                'data' => new InternshipResource($internship)
            ]);
        });
    }
}
