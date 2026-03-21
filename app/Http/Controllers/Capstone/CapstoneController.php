<?php

namespace App\Http\Controllers\Capstone;

use App\Http\Controllers\Controller;
use App\Models\{Capstone, Council, CapstoneRequest, Lecturer, Milestone, Notification, UserNotification, LecturerLeave, CapstoneReviewer};
use App\Http\Requests\Capstone\ConfirmRegistrationRequest;
use App\Http\Resources\Capstone\CapstoneRegistrationResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Requests\Capstone\ReviewTopicRequest;
use App\Http\Resources\Capstone\ProposedTopicResource;
use App\Models\{CapstoneReport};
use App\Http\Requests\Capstone\ApproveCapstoneReportRequest;
use App\Http\Resources\Capstone\CapstoneReportDetailResource;
use App\Http\Requests\Capstone\SubmitCapstoneGradeRequest;
use App\Http\Resources\Capstone\CapstoneGradingResource;
use App\Http\Requests\Capstone\SubmitReviewGradeRequest;
use App\Http\Requests\Capstone\ReviewCancellationRequest;
use App\Http\Resources\Capstone\CapstoneReviewResource;
use App\Http\Requests\Capstone\AssignSupervisorRequest;
use App\Http\Resources\Capstone\LecturerSlotResource;
use App\Http\Resources\Capstone\CapstoneCancellationResource;
use App\Http\Resources\Capstone\CapstoneStatisticsResource;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Topic;
use App\Http\Resources\CapstoneResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CapstoneController extends Controller
{
    /**
     * UC 23 - Lấy danh sách đăng ký hướng dẫn
     */
    public function getPendingRegistrations()
    {
        $lecturerId = auth()->id();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // Kiểm tra nghỉ phép - Xử lý lỗi ambiguous status
        if ($lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists()) {
            return response()->json(['message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép.'], 403);
        }

        // Kiểm tra thời hạn theo start_date và end_date
        $milestone = Milestone::capstone()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$milestone) {
            return response()->json(['message' => 'Hiện không nằm trong thời gian đăng ký GVHD.'], 400);
        }

        $requests = CapstoneRequest::where('lecturer_id', $lecturerId)
            ->whereIn('type', [
                CapstoneRequest::TYPE_LECTURER_REG,
                CapstoneRequest::TYPE_TOPIC_BANK,
            ])
            ->where('status', CapstoneRequest::STATUS_PENDING_TEACHER)
            ->orderBy('created_at', 'asc')
            ->with(['capstone.student.studentClass'])
            ->get();

        return CapstoneRegistrationResource::collection($requests);
    }

    /**
     * Bước 4-8: Xử lý Xác nhận
     */
    public function confirmRegistration(ConfirmRegistrationRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $lecturerId = auth()->id();
            $lecturer = Lecturer::findOrFail($lecturerId);
            $capReq = CapstoneRequest::where('lecturer_id', $lecturerId)
                ->whereIn('type', [
                    CapstoneRequest::TYPE_LECTURER_REG,
                    CapstoneRequest::TYPE_TOPIC_BANK,
                ])
                ->findOrFail($id);

            $feedback = trim((string) $request->input('feedback', ''));

            if ($request->action === 'APPROVE') {
                // 5a. Kiểm tra giới hạn slot (BR-2: Giả định 30)
                $maxSlots = 30;
                if ($lecturer->capstones()->count() >= $maxSlots) {
                    return response()->json(['message' => 'Đã nhận đủ số lượng sinh viên tối đa (5a1).'], 400);
                }

                // Bước 6: Cập nhật giảng viên vào đồ án & Trạng thái yêu cầu
                $capReq->update([
                    'status' => CapstoneRequest::STATUS_APPROVED,
                    'lecturer_feedback' => $feedback !== '' ? $feedback : null
                ]);
                $capReq->capstone->update([
                    'lecturer_id' => $lecturerId,
                    'status'      => Capstone::STATUS_LECTURER_APPROVED
                ]);

                $notifyMsg = "Giảng viên {$lecturer->full_name} đã chấp nhận hướng dẫn đồ án của bạn.";
            } else {
                // 4a. Từ chối
                $capReq->update([
                    'status' => CapstoneRequest::STATUS_REJECTED,
                    'lecturer_feedback' => $feedback !== '' ? $feedback : null
                ]);
                $notifyMsg = "Giảng viên {$lecturer->full_name} đã từ chối yêu cầu hướng dẫn đồ án.";
            }

            if ($feedback !== '') {
                $notifyMsg .= " Lời nhắn: {$feedback}";
            }

            // Bước 8: Gửi thông báo cho sinh viên
            $notification = Notification::create([
                'title'   => 'Kết quả đăng ký GVHD Đồ án',
                'content' => $notifyMsg
            ]);

            UserNotification::create([
                'notification_id' => $notification->notification_id,
                'user_id'         => $capReq->capstone->student_id,
                'role_id'         => 1, // Student Role ID
            ]);

            return response()->json(['success' => true, 'message' => 'Xử lý xác nhận thành công.']);
        });
    }
    // UC 28
    /**
     * Bước 5: Lấy danh sách giảng viên và số slot để VPK lựa chọn
     */
    public function getLecturerAssignmentList()
    {
        // Lấy giảng viên kèm số lượng đồ án đang hướng dẫn
        $lecturers = Lecturer::withCount('capstones')->get();
        return LecturerSlotResource::collection($lecturers);
    }

    /**
     * Bước 7-12: Thực hiện phân công GVHD hàng loạt
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
            $maxSlots = 30;
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


    /**
     * UC 24.1 - Danh sách đề tài chờ duyệt (Giảng viên)
     */
    public function getPendingTopicsLecturer()
    {
        $user = auth()->user();
        $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
        $lecturer = Lecturer::findOrFail($lecturerId);

        if ($lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists()) {
            return response()->json(['message' => 'Bạn đang trong trạng thái nghỉ phép.'], 403);
        }

        // Kiểm tra thời hạn theo end_date
        $milestone = Milestone::capstone()->where('end_date', '>', now())->first();
        if (!$milestone) {
            return response()->json(['message' => 'Thời gian phê duyệt đề tài đã kết thúc.'], 400);
        }

        $requests = CapstoneRequest::where('type', CapstoneRequest::TYPE_TOPIC_PROP)
            ->where('status', CapstoneRequest::STATUS_PENDING_TEACHER)
            ->whereHas('capstone', function ($query) use ($lecturerId) {
                $query->where('lecturer_id', $lecturerId);
            })
            ->with(['capstone.student.studentClass', 'proposedTopic'])
            ->get();

        return ProposedTopicResource::collection($requests);
    }

    /**
     * Bước 6-9: Giảng viên duyệt/từ chối
     */
    public function reviewTopicLecturer(ReviewTopicRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = auth()->user();
            $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
            $capReq = CapstoneRequest::where('type', CapstoneRequest::TYPE_TOPIC_PROP)
                ->where('status', CapstoneRequest::STATUS_PENDING_TEACHER)
                ->whereHas('capstone', function ($query) use ($lecturerId) {
                    $query->where('lecturer_id', $lecturerId);
                })
                ->findOrFail($id);

            if ($request->status === 'APPROVED') {
                // Bước 8: Chuyển trạng thái chờ VPK duyệt
                $capReq->update([
                    'status' => CapstoneRequest::STATUS_PENDING_FACULTY,
                    'lecturer_feedback' => $request->feedback,
                ]);
                $msg = "Đề tài của bạn đã được Giảng viên duyệt và đang chờ Văn phòng khoa phê duyệt cuối cùng.";
            } else {
                // 6a: Từ chối
                $capReq->update([
                    'status' => CapstoneRequest::STATUS_REJECTED,
                    'lecturer_feedback' => $request->feedback
                ]);
                $msg = "Đề tài của bạn đã bị Giảng viên từ chối.";
            }

            $this->notifyStudent($capReq->capstone->student_id, 1, $msg);
            return response()->json(['success' => true, 'message' => 'Xử lý thành công.']);
        });
    }

    // ===================== 24.2: VPK PHÊ DUYỆT =====================

    /**
     * Bước 3: Danh sách đề tài đã qua GV duyệt (Dành cho VPK)
     */
    public function getPendingTopicsVPK()
    {
        // Kiểm tra thời hạn VPK duyệt (có thể lấy từ Milestone riêng)
        $requests = CapstoneRequest::where('status', CapstoneRequest::STATUS_PENDING_FACULTY)
            ->with(['capstone.student.studentClass', 'proposedTopic'])
            ->get();

        return ProposedTopicResource::collection($requests);
    }

    /**
     * Bước 4-7: VPK duyệt cuối cùng
     */
    public function confirmTopicVPK(ReviewTopicRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $capReq = CapstoneRequest::where('status', CapstoneRequest::STATUS_PENDING_FACULTY)->findOrFail($id);

            if ($request->status === 'APPROVED') {
                $capReq->update(['status' => CapstoneRequest::STATUS_APPROVED]);

                // Cập nhật vào bảng Capstone (BR-1 phân hệ 24.2)
                $capstone = $capReq->capstone;
                $capstone->update(['status' => Capstone::STATUS_TOPIC_APPROVED]);

                $msg = "Đề tài đồ án của bạn đã chính thức được Văn phòng khoa phê duyệt.";
            } else {
                $capReq->update(['status' => CapstoneRequest::STATUS_REJECTED, 'lecturer_feedback' => $request->feedback]);
                $msg = "Văn phòng khoa đã từ chối đề tài của bạn.";
            }

            $this->notifyStudent($capReq->capstone->student_id, $msg);
            return response()->json(['success' => true, 'message' => 'Phê duyệt thành công.']);
        });
    }

    // UC 25
    /**
     * Bước 3: Danh sách báo cáo cần duyệt (Chỉ những SV mình hướng dẫn - BR-1)
     */
    public function getPendingReports()
    {
        $lecturerId = auth()->id();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // 2a: Kiểm tra trạng thái nghỉ phép (Sửa lỗi ambiguous status)
        if ($lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists()) {
            return response()->json(['message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép (2a1).'], 403);
        }

        // BR-1: Chỉ lấy báo cáo của các đồ án do giảng viên này hướng dẫn
        $reports = CapstoneReport::whereHas('capstone', function ($query) use ($lecturerId) {
            $query->where('lecturer_id', $lecturerId);
        })
            ->where('status', CapstoneReport::STATUS_PENDING) // Chỉ lấy báo cáo chưa duyệt
            ->with(['capstone.student', 'capstone.topic', 'milestone'])
            ->get();

        return CapstoneReportDetailResource::collection($reports);
    }

    /**
     * Bước 6-9: Thực hiện phê duyệt/từ chối báo cáo
     */
    public function approveReport(ApproveCapstoneReportRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $lecturerId = auth()->id();

            // Tìm báo cáo và kiểm tra quyền hướng dẫn (BR-1)
            $report = CapstoneReport::whereHas('capstone', function ($query) use ($lecturerId) {
                $query->where('lecturer_id', $lecturerId);
            })->findOrFail($id);

            // BR-3: Không cho phép thay đổi nếu báo cáo đã được duyệt/từ chối trước đó
            if ($report->status !== CapstoneReport::STATUS_PENDING) {
                return response()->json(['message' => 'Báo cáo này đã được xử lý và không thể thay đổi nội dung (BR-3).'], 400);
            }

            // Bước 7: Cập nhật trạng thái và nhận xét
            $report->update([
                'status'            => $request->status,
                'lecturer_feedback' => $request->feedback,
                'updated_at'        => Carbon::now()
            ]);

            // Bước 9: Gửi thông báo cho sinh viên
            $statusText = $request->status === 'APPROVED' ? 'được phê duyệt' : 'bị từ chối';
            $notification = Notification::create([
                'title'   => 'Kết quả duyệt báo cáo đồ án',
                'content' => "Báo cáo giai đoạn của bạn đã {$statusText} bởi Giảng viên hướng dẫn."
            ]);

            UserNotification::create([
                'notification_id' => $notification->notification_id,
                'user_id'         => $report->capstone->student_id,
                'role_id'         => 1, // Student
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái báo cáo thành công (Bước 8).',
                'data'    => new CapstoneReportDetailResource($report)
            ]);
        });
    }
    // UC 26
    /**
     * Bước 3: Hiển thị danh sách sinh viên đã nộp báo cáo (theo giảng viên hướng dẫn)
     */
    public function getGradingList()
    {
        $lecturerId = auth()->id();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // 2a: Kiểm tra trạng thái nghỉ phép
        if ($lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists()) {
            return response()->json(['message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép (2a1).'], 403);
        }

        // 2b: Kiểm tra thời hạn chấm điểm (Dùng cột end_date thay cho deadline)
        $milestone = Milestone::capstone()->where('end_date', '>', now())->first();
        if (!$milestone) {
            return response()->json(['message' => 'Đã hết thời hạn chấm điểm (2b1).'], 400);
        }

        // BR-1: Chỉ giảng viên được phân công mới thấy danh sách của mình
        $students = Capstone::where('lecturer_id', $lecturerId)
            ->whereHas('reports') // Chỉ những SV đã nộp báo cáo
            ->with(['student.studentClass', 'topic.expertise'])
            ->get();

        return CapstoneGradingResource::collection($students);
    }

    /**
     * Bước 8-12: Thực hiện chấm điểm và cập nhật trạng thái
     */
    public function submitGrade(SubmitCapstoneGradeRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $lecturerId = auth()->id();

            // Tìm đồ án và kiểm tra quyền chấm điểm (BR-1)
            $capstone = Capstone::where('lecturer_id', $lecturerId)->findOrFail($id);

            // BR-2: Không cho phép sửa điểm sau khi đã gửi thành công
            if ($capstone->instructor_grade !== null) {
                return response()->json(['message' => 'Đồ án này đã có điểm và không thể chỉnh sửa (BR-2).'], 400);
            }

            // Xử lý logic cập nhật trạng thái (BR-4)
            $grade = (float) $request->grade;
            $newStatus = ($grade >= 5.5) ? Capstone::STATUS_COMPLETED : Capstone::STATUS_FAILED;

            // Bước 9: Cập nhật dữ liệu
            $capstone->update([
                'instructor_grade' => $grade,
                'status'           => $newStatus,
                'updated_at'       => Carbon::now()
            ]);

            // Bước 12: Gửi thông báo cho sinh viên
            $resultText = ($grade >= 5.5) ? "Đạt" : "Trượt";
            $notification = Notification::create([
                'title'   => 'Thông báo kết quả điểm đồ án',
                'content' => "Giảng viên đã chấm điểm đồ án của bạn. Điểm: {$grade}. Kết quả: {$resultText}."
            ]);

            UserNotification::create([
                'notification_id' => $notification->notification_id,
                'user_id'         => $capstone->student_id,
                'role_id'         => 1, // Student Role
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật điểm thành công (Bước 10).',
                'data'    => [
                    'grade'  => $grade,
                    'status' => $newStatus
                ]
            ]);
        });
    }
    // UC 27
    /**
     * Bước 3: Danh sách sinh viên được phân công phản biện
     */
    public function getReviewingList()
    {
        $user = auth()->user();
        $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // 2a. Ngoại lệ: Nghỉ phép
        if ($lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists()) {
            return response()->json(['message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép.'], 403);
        }

        // 2b. Ngoại lệ: Hết hạn chấm điểm
        $milestone = Milestone::capstone()->where('end_date', '>', now())->first();
        if (!$milestone) {
            return response()->json(['message' => 'Đã hết thời hạn chấm điểm phản biện.'], 400);
        }

        // BR-1: Lấy danh sách từ bảng capstone_reviewers
        $assignments = CapstoneReviewer::where('lecturer_id', $lecturerId)
            ->with(['capstone.student.studentClass', 'capstone.topic.expertise'])
            ->get();

        return CapstoneReviewResource::collection($assignments);
    }

    /**
     * Bước 7-12: Thực hiện chấm điểm phản biện
     */
    public function submitReviewGrade(SubmitReviewGradeRequest $request, $capstoneId)
    {
        return DB::transaction(function () use ($request, $capstoneId) {
            $user = auth()->user();
            $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();

            // BR-1: Kiểm tra quyền phản biện
            $reviewRecord = CapstoneReviewer::where('capstone_id', $capstoneId)
                ->where('lecturer_id', $lecturerId)
                ->firstOrFail();

            // BR-2: Không cho phép sửa điểm sau khi đã gửi
            if ($reviewRecord->opponent_grade !== null) {
                return response()->json(['message' => 'Bạn đã nộp điểm phản biện cho đồ án này và không thể sửa đổi.'], 400);
            }

            // Bước 9: Lưu điểm vào bảng capstone_reviewers
            $reviewRecord->update([
                'opponent_grade' => $request->grade,
                'opponent_feedback' => $request->feedback,
            ]);

            // Bước 10: Kiểm tra nếu tất cả GV phản biện đã chấm xong
            $capstone = Capstone::findOrFail($capstoneId);
            $allReviewers = $capstone->reviewers();
            $gradedReviewers = $capstone->reviewers()->whereNotNull('opponent_grade');

            if ($allReviewers->count() === $gradedReviewers->count()) {
                // Tính điểm trung bình phản biện
                $avgGrade = $gradedReviewers->avg('opponent_grade');

                // Bước 11: Cập nhật trạng thái đồ án (BR-4)
                // Theo đặc tả: < 5.5 = Trượt (FAILED), >= 5.5 = Đạt (DEFENSE_ELIGIBLE/COMPLETED)
                $newStatus = ($avgGrade >= 5.5) ? Capstone::STATUS_DEFENSE_ELIGIBLE : Capstone::STATUS_FAILED;

                $capstone->update(['status' => $newStatus]);

                // Bước 12: Gửi thông báo kết quả cuối cùng cho SV
                $resultText = ($avgGrade >= 5.5) ? "Đạt điều kiện bảo vệ" : "Trượt phản biện";
                $this->notifyStudent($capstone->student_id, 1, "Kết quả phản biện đồ án: {$resultText}. Điểm trung bình: {$avgGrade}");
            } else {
                // Nếu chưa đủ người chấm, chỉ thông báo là đã ghi nhận điểm của GV này
                $this->notifyStudent($capstone->student_id, 1, "Giảng viên phản biện đã cập nhật điểm đánh giá cho đồ án của bạn.");
            }

            return response()->json(['success' => true, 'message' => 'Lưu điểm phản biện thành công.']);
        });
    }

    // UC 29
    /**
     * Bước 3 & 5: Lấy danh sách hội đồng và thành viên cho VPK
     */
    public function getCouncilsForAssignment()
    {
        $councils = Council::with('members')->get();
        return CouncilAssignmentResource::collection($councils);
    }

    /**
     * Bước 7-12: Thực hiện phân công Hội đồng và 2 GVPB
     */
    public function assignCouncilAndReviewers(AssignCouncilRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $councilId = $request->council_id;
            $reviewerIds = $request->reviewer_ids;
            $studentIds = $request->student_ids;

            $council = Council::findOrFail($councilId);

            // 5a. Kiểm tra slot của hội đồng (Giả định mỗi hội đồng tối đa 10 SV/ngày)
            if ($council->capstones()->count() + count($studentIds) > 10) {
                return response()->json(['message' => "Hội đồng {$council->name} không đủ slot báo cáo (5a1)."], 400);
            }

            // Kiểm tra trạng thái nghỉ phép của GVPB (8c)
            foreach ($reviewerIds as $rId) {
                $rev = Lecturer::findOrFail($rId);
                if ($rev->leaves()->where('lecturer_leaves.status', 'LEAVE_ACTIVE')->exists()) {
                    return response()->json(['message' => "Giảng viên {$rev->full_name} đang nghỉ phép, không thể phân công (8c1)."], 400);
                }

                // 8b. Kiểm tra slot phản biện của GV (Giả định mỗi GV phản biện tối đa 15 SV/đợt)
                if ($rev->capstoneReviewers()->count() + count($studentIds) > 15) {
                    return response()->json(['message' => "Giảng viên {$rev->full_name} không đủ slot phản biện (8b1)."], 400);
                }
            }

            foreach ($studentIds as $sId) {
                $capstone = Capstone::where('student_id', $sId)->firstOrFail();

                // 8a. Kiểm tra trùng GVHD (BR-1)
                if (in_array($capstone->lecturer_id, $reviewerIds)) {
                    return response()->json(['message' => "Giảng viên phản biện không được trùng với Giảng viên hướng dẫn của SV ID: {$sId} (8a1)."], 400);
                }

                // Bước 9: Cập nhật Hội đồng cho đồ án
                $capstone->update([
                    'council_id' => $councilId,
                    'status'     => Capstone::STATUS_REVIEW_ELIGIBLE
                ]);

                // Bước 9: Xóa phản biện cũ (nếu có) và tạo mới 2 GVPB (BR-2)
                CapstoneReviewer::where('capstone_id', $capstone->capstone_id)->delete();
                foreach ($reviewerIds as $rId) {
                    CapstoneReviewer::create([
                        'capstone_id' => $capstone->capstone_id,
                        'lecturer_id' => $rId
                    ]);

                    // Thông báo cho GVPB
                    $this->notifyStudent($rId, 2, "Bạn được phân công phản biện đồ án cho sinh viên {$capstone->student->full_name}.");
                }

                // Thông báo cho Sinh viên
                $this->notifyStudent($sId, 1, "Bạn đã được phân công vào hội đồng {$council->name} và có giảng viên phản biện.");
            }

            return response()->json(['success' => true, 'message' => 'Phân công hội đồng và giảng viên phản biện thành công.']);
        });
    }
    /**
     * UC 30: Sinh viên gửi yêu cầu hủy đồ án
     */
    public function requestCancel(Request $request)
    {
        // 1. Lấy thông tin sinh viên đang đăng nhập
        $studentId = auth()->id();

        // 2. Tìm đồ án của sinh viên (đảm bảo sinh viên có đồ án trong hệ thống)
        $capstone = Capstone::where('student_id', $studentId)->first();

        if (!$capstone) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn hiện không có học phần đồ án nào để yêu cầu hủy.'
            ], 404);
        }

        // 3. Kiểm tra trạng thái hiện tại (nếu đã gửi yêu cầu rồi thì không cho gửi lại)
        if ($capstone->status === 'PENDING_CANCEL') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã gửi yêu cầu hủy trước đó, vui lòng chờ xử lý.'
            ], 400);
        }

        // 4. Kiểm tra thời hạn (BR-1: Trong vòng 14 ngày kể từ ngày bắt đầu đợt đồ án)
        $milestone = Milestone::where('semester_id', $capstone->semester_id)
            ->where('type', 'CAPSTONE')
            ->first();

        if (!$milestone) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin đợt đồ án để kiểm tra thời hạn.'
            ], 404);
        }

        $startDate = Carbon::parse($milestone->start_date);
        $now = Carbon::now();

        // Kiểm tra logic 14 ngày (BR-1)
        if ($now->diffInDays($startDate, false) > 14 || $now->lt($startDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Đã hết thời gian yêu cầu hủy học phần đồ án tốt nghiệp (4a1).'
            ], 400);
        }

        return DB::transaction(function () use ($capstone) {
            // 5. Cập nhật trạng thái đồ án sang Chờ duyệt hủy
            // Sử dụng chuỗi trực tiếp để không cần sửa file Model Capstone.php
            $capstone->update([
                'status' => 'PENDING_CANCEL'
            ]);

            // 6. Gửi thông báo cho Văn phòng khoa (Role ID: 3)
            // Giả định gửi cho một nhân viên quản lý hoặc thông báo chung hệ thống
            $this->sendNotification(
                1, // ID của nhân viên VPK (hoặc tùy biến theo logic phân quyền của bạn)
                3, // Role VPK
                "Sinh viên {$capstone->student->full_name} đã gửi yêu cầu hủy học phần đồ án."
            );

            return response()->json([
                'success' => true,
                'message' => 'Gửi yêu cầu hủy học phần thành công, vui lòng chờ Văn phòng khoa phê duyệt.'
            ]);
        });
    }

    /**
     * Hàm helper gửi thông báo (Giữ nguyên duy nhất 1 hàm này ở cuối Class)
     */
    private function sendNotification($userId, $roleId, $content)
    {
        $notification = Notification::create([
            'title'   => 'Thông báo hệ thống Đồ án',
            'content' => $content
        ]);

        UserNotification::create([
            'notification_id' => $notification->notification_id,
            'user_id'         => $userId,
            'role_id'         => $roleId,
        ]);
    }
// =========================================================================
    // 31.1. PHÂN HỆ GIẢNG VIÊN
    // =========================================================================

    /**
     * Hiển thị danh sách sinh viên yêu cầu hủy (Dành cho GV hướng dẫn)
     */
    public function getPendingCancellationsLecturer()
    {
        $user = auth()->user();
        $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // Kiểm tra trạng thái nghỉ phép (Ngoại lệ 2a)
        if ($lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists()) {
            return response()->json(['message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép.'], 403);
        }

        // BR-1: Chỉ lấy các yêu cầu của sinh viên mình hướng dẫn
        $list = Capstone::where('lecturer_id', $lecturerId)
            ->where('status', 'PENDING_CANCEL')
            ->with(['student.studentClass', 'topic'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $list->map(function ($capstone) {
                return [
                    'request_id' => $capstone->capstone_id,
                    'capstone_id' => $capstone->capstone_id,
                    'student_code' => $capstone->student->usercode ?? 'N/A',
                    'student_name' => $capstone->student->full_name ?? 'N/A',
                    'class_name' => $capstone->student->studentClass->class_name ?? 'N/A',
                    'topic_title' => $capstone->topic->title ?? 'N/A',
                    'reason' => 'Sinh viên yêu cầu hủy đồ án',
                    'status' => $capstone->status,
                    'created_at' => optional($capstone->updated_at)->format('Y-m-d H:i:s'),
                    'topic' => $capstone->topic ? [
                        'topic_id' => $capstone->topic->topic_id,
                        'title' => $capstone->topic->title,
                        'description' => $capstone->topic->description,
                        'technologies' => $capstone->topic->technologies,
                    ] : null,
                ];
            })
        ]);
    }

    /**
     * Giảng viên Duyệt hoặc Từ chối yêu cầu hủy
     */
    public function reviewCancellationLecturer(ReviewCancellationRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = auth()->user();
            $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
            $capstone = Capstone::where('lecturer_id', $lecturerId)
                ->where('status', 'PENDING_CANCEL')
                ->findOrFail($id);

            if ($request->status === 'APPROVED') {
                // Duyệt: Chuyển sang trạng thái chờ VPK duyệt hủy
                $capstone->update(['status' => 'PENDING_FACULTY_CANCEL']);

                // Gửi thông báo cho VPK (Giả định Role VPK ID là 3)
                $this->notifyStudent(1, 3, "Có yêu cầu hủy đồ án của SV {$capstone->student->full_name} cần bạn phê duyệt.");

                return response()->json(['success' => true, 'message' => 'Đã duyệt yêu cầu và chuyển cấp Văn phòng khoa.']);
            } else {
                // Từ chối: Trả về trạng thái hoạt động bình thường (Giả định TOPIC_APPROVED)
                $capstone->update(['status' => Capstone::STATUS_TOPIC_APPROVED]);

                // Gửi thông báo cho sinh viên (Role SV ID là 1)
                $this->notifyStudent($capstone->student_id, 1, "Giảng viên hướng dẫn đã từ chối yêu cầu hủy đồ án của bạn.");

                return response()->json(['success' => true, 'message' => 'Đã từ chối yêu cầu hủy đồ án.']);
            }
        });
    }

    // =========================================================================
    // 31.2. PHÂN HỆ VĂN PHÒNG KHOA
    // =========================================================================

    /**
     * Hiển thị danh sách chờ VPK phê duyệt hủy
     */
    public function getPendingCancellationsVPK()
    {
        $list = Capstone::where('status', 'PENDING_FACULTY_CANCEL')
            ->with(['student.studentClass', 'topic', 'lecturer'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => CapstoneCancellationResource::collection($list),
        ]);
    }

    /**
     * UC 22 - BƯỚC 2: Student registers for a topic from the topic bank
     * POST /capstones/register-topic
     */
    
    public function registerTopic(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,topic_id',
            'lecturer_id' => 'required|exists:lecturers,lecturer_id',
        ]);

        $user = Auth::user();
        if (!$user || get_class($user) !== \App\Models\Student::class) {
            return response()->json(['success' => false, 'message' => 'Chỉ sinh viên mới có thể đăng ký'], 403);
        }

        return DB::transaction(function () use ($validated, $user) {
            // Check if student already has an active capstone
            $existingCapstone = Capstone::where('student_id', $user->student_id)
                ->whereNotIn('status', [Capstone::STATUS_CANCEL, Capstone::STATUS_FAILED])
                ->first();

            if ($existingCapstone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã đăng ký đề tài đồ án. Không thể đăng ký thêm.'
                ], 400);
            }

            // Create new capstone record
            $capstone = Capstone::create([
                'topic_id' => $validated['topic_id'],
                'student_id' => $user->student_id,
                'status' => Capstone::STATUS_INITIALIZED,
            ]);

            // Create capstone request
            CapstoneRequest::create([
                'capstone_id' => $capstone->capstone_id,
                'topic_id' => $validated['topic_id'],
                'lecturer_id' => $validated['lecturer_id'],
                'type' => CapstoneRequest::TYPE_TOPIC_BANK,
                'status' => CapstoneRequest::STATUS_PENDING_TEACHER,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã gửi yêu cầu đăng ký đề tài. Vui lòng chờ giảng viên xác nhận.',
                'data' => [
                    'capstone_id' => $capstone->capstone_id,
                    'topic_id' => $capstone->topic_id,
                    'status' => $capstone->status,
                ]
            ], 201);
        });
    }
    

    /**
     * UC 22 - BƯỚC 1: Student proposes a new topic
     * POST /capstones/propose-topic
     */
    public function proposeTopic(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:10|max:255',
            'description' => 'nullable|string|max:500',
            'technologies' => 'nullable|string',
            'expertise_id' => 'nullable|exists:expertises,expertise_id',
            'file_path' => 'nullable|string',
        ]);

        $user = Auth::user();
        if (!$user || get_class($user) !== \App\Models\Student::class) {
            return response()->json(['success' => false, 'message' => 'Chỉ sinh viên mới có thể đề xuất'], 403);
        }

        return DB::transaction(function () use ($validated, $user) {
            // Check if student already has an active capstone
            $existingCapstone = Capstone::where('student_id', $user->student_id)
                ->whereNotIn('status', [Capstone::STATUS_CANCEL, Capstone::STATUS_FAILED])
                ->first();

            if ($existingCapstone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã đăng ký đề tài đồ án. Không thể đề xuất thêm.'
                ], 400);
            }

            // Create topic (proposed by student)
            $topic = Topic::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'technologies' => $validated['technologies'],
                'expertise_id' => $validated['expertise_id'],
                'student_id' => $user->student_id, // Link to proposing student
            ]);

            // Create capstone record
            $capstone = Capstone::create([
                'topic_id' => $topic->topic_id,
                'student_id' => $user->student_id,
                'status' => Capstone::STATUS_INITIALIZED,
            ]);

            // Create capstone request - waiting for faculty to assign lecturer
            CapstoneRequest::create([
                'capstone_id' => $capstone->capstone_id,
                'topic_id' => $topic->topic_id,
                'type' => CapstoneRequest::TYPE_TOPIC_PROP,
                'status' => CapstoneRequest::STATUS_PENDING_FACULTY,
                'file_path' => $validated['file_path'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã gửi đề xuất đề tài. Vui lòng chờ xác nhận từ nhà trường.',
                'data' => [
                    'capstone_id' => $capstone->capstone_id,
                    'topic_id' => $topic->topic_id,
                    'status' => $capstone->status,
                ]
            ], 201);
        });
    }

    /**
     * UC 22 - Get student's capstone requests
     * GET /capstones/my-requests
     */
    public function getMyRequests()
    {
        $user = Auth::user();
        if (!$user || get_class($user) !== \App\Models\Student::class) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $requests = CapstoneRequest::with(['capstone.topic', 'lecturer', 'topic'])
            ->whereHas('capstone', function ($query) use ($user) {
                $query->where('student_id', $user->student_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    /**
     * VPK phê duyệt cuối cùng
     */
    public function reviewCancellationVPK(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $capstone = Capstone::where('status', 'PENDING_FACULTY_CANCEL')->findOrFail($id);

            if ($request->action === 'APPROVE') {
                // BR-1: Phê duyệt hủy cuối cùng, giải phóng slot (Trạng thái CANCEL)
                $capstone->update(['status' => Capstone::STATUS_CANCEL]);

                $msg = "Học phần đồ án của bạn đã chính thức được hủy trên hệ thống.";
                $resMsg = "Phê duyệt hủy đồ án thành công.";
            } else {
                // VPK từ chối: Trả về trạng thái hoạt động bình thường
                $capstone->update(['status' => Capstone::STATUS_TOPIC_APPROVED]);

                $msg = "Văn phòng khoa đã từ chối yêu cầu hủy đồ án của bạn.";
                $resMsg = "Đã từ chối yêu cầu hủy đồ án.";
            }

            // Gửi thông báo cho sinh viên
            $this->notifyStudent($capstone->student_id, 1, $msg);

            return response()->json(['success' => true, 'message' => $resMsg]);
        });
    }
    /**
     * UC 32: Thống kê và lọc danh sách đồ án
     */
    public function indexStatistics(Request $request)
    {
        // BR-1: Mặc định chọn học kỳ mới nhất nếu không truyền tham số lọc
        $semesterId = $request->input('semester_id');
        if (!$semesterId) {
            $latestSemester = Semester::orderBy('year_start', 'desc')->orderBy('semester_number', 'desc')->first();
            $semesterId = $latestSemester ? $latestSemester->semester_id : null;
        }

        $query = Capstone::with(['student.studentClass', 'lecturer', 'reviewers.lecturer', 'council'])
            ->where('semester_id', $semesterId);

        // Áp dụng các tiêu chí lọc (Luồng chính bước 4)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('lecturer_id')) {
            $query->where('lecturer_id', $request->lecturer_id);
        }
        if ($request->filled('council_id')) {
            $query->where('council_id', $request->council_id);
        }
        if ($request->filled('reviewer_id')) {
            $query->whereHas('reviewers', function ($q) use ($request) {
                $q->where('lecturer_id', $request->reviewer_id);
            });
        }

        $capstones = $query->get();

        return response()->json([
            'success' => true,
            'semester_id' => $semesterId,
            'data' => CapstoneStatisticsResource::collection($capstones)
        ]);
    }

    /**
     * UC 32: Xuất báo cáo Excel (Logic chuẩn bị dữ liệu)
     */
    public function exportStatistics(Request $request)
    {
        // Tái sử dụng logic lọc từ hàm indexStatistics
        $semesterId = $request->input('semester_id');
        $semesterCode = Semester::find($semesterId)->semester_id ?? 'unknown'; // Giả định dùng ID làm mã học kỳ

        // NFR-1: Tên file bao gồm mã học kỳ và ngày xuất
        $fileName = "Bao-cao-do-an-ky-" . $semesterCode . "-" . Carbon::now()->format('Ymd') . ".xlsx";

        // Trong thực tế, bạn sẽ sử dụng thư viện như Maatwebsite/Laravel-Excel
        // return Excel::download(new CapstoneExport($request->all()), $fileName);

        return response()->json([
            'success' => true,
            'file_name' => $fileName,
            'download_url' => url("/storage/exports/{$fileName}") // Link tải tệp cho trình duyệt
        ]);
    }

    private function notifyStudent($userId, $roleId, $content)
    {
        $notification = \App\Models\Notification::create([
            '   title'   => 'Thông báo hệ thống Đồ án',
            'content' => $content
        ]);

        \App\Models\UserNotification::create([
            'notification_id' => $notification->notification_id,
            'user_id'         => $userId,
            'role_id'         => $roleId // Sử dụng biến $roleId truyền vào
        ]);
    }
    /**
     * UC 22 - Get student's capstone status
     * GET /capstones/my-status
     */
    public function getMyCapstoneStatus()
    {
        $user = Auth::user();
        if (!$user || get_class($user) !== \App\Models\Student::class) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $capstone = Capstone::with(['topic.lecturer', 'topic.expertise', 'lecturer'])
            ->where('student_id', $user->student_id)
            ->whereNotIn('status', [Capstone::STATUS_CANCEL, Capstone::STATUS_FAILED])
            ->first();

        if (!$capstone) {
            return response()->json([
                'success' => true,
                'message' => 'Bạn chưa đăng ký đề tài đồ án',
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'capstone_id' => $capstone->capstone_id,
                'status' => $capstone->status,
                'topic' => [
                    'topic_id' => $capstone->topic->topic_id,
                    'title' => $capstone->topic->title,
                    'description' => $capstone->topic->description,
                    'technologies' => $capstone->topic->technologies,
                    'expertise' => $capstone->topic->expertise ? [
                        'expertise_id' => $capstone->topic->expertise->expertise_id,
                        'name' => $capstone->topic->expertise->name,
                    ] : null,
                ],
                'lecturer' => $capstone->lecturer ? [
                    'lecturer_id' => $capstone->lecturer->lecturer_id,
                    'full_name' => $capstone->lecturer->full_name,
                ] : null,
                'created_at' => $capstone->created_at,
            ],
        ]);
    }
}

