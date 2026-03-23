<?php

namespace App\Http\Controllers\Capstone;

use App\Models\{Capstone, CapstoneReviewer, Lecturer, Milestone, LecturerLeave};
use App\Http\Requests\Capstone\SubmitReviewGradeRequest;
use App\Http\Resources\Capstone\CapstoneReviewResource;
use Illuminate\Support\Facades\DB;

class ReviewController extends CapstoneBaseController
{
    /**
     * UC 27 - Bước 3: Danh sách sinh viên được phân công phản biện
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
     * UC 27 - Bước 7-12: Thực hiện chấm điểm phản biện
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
}
