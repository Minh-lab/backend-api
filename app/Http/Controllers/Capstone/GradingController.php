<?php

namespace App\Http\Controllers\Capstone;

use App\Models\{Capstone, Lecturer, Milestone, LecturerLeave};
use App\Http\Requests\Capstone\SubmitCapstoneGradeRequest;
use App\Http\Resources\Capstone\CapstoneGradingResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradingController extends CapstoneBaseController
{
    /**
     * UC 26 - Bước 3: Hiển thị danh sách sinh viên đã nộp báo cáo (theo giảng viên hướng dẫn)
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
     * UC 26 - Bước 8-12: Thực hiện chấm điểm và cập nhật trạng thái
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
            $msg = "Giảng viên đã chấm điểm đồ án của bạn. Điểm: {$grade}. Kết quả: {$resultText}.";

            $this->notifyStudent($capstone->student_id, 1, $msg);

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
}
