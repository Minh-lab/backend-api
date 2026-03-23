<?php

namespace App\Http\Controllers\Internship;

use App\Models\{Internship, Milestone, Lecturer, Notification, UserNotification};
use App\Http\Requests\Internship\GradeInternshipRequest;
use App\Http\Resources\Internship\InternshipGradeResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradingController extends InternshipBaseController
{
    /**
     * UC 41 - Bước 3: Danh sách sinh viên cần chấm điểm
     */
    public function getStudentsForGrading()
    {
        $user = auth()->user();
        $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // 2a: Kiểm tra trạng thái nghỉ phép
        if ($lecturer->leaves()->active()->exists()) {
            return response()->json(['message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép (2a1).'], 403);
        }

        // BR-2: Sinh viên đã nộp báo cáo và trong thời hạn chấm điểm
        $gradingMilestone = Milestone::where('type', Milestone::TYPE_INTERNSHIP)->upcoming()->first();
        if (!$gradingMilestone) {
            return response()->json(['message' => 'Ngoài thời hạn chấm điểm quy định (BR-2).'], 400);
        }

        $students = Internship::where('lecturer_id', $lecturerId)
            ->whereHas('reports') // BR-2: Đã nộp báo cáo
            ->with(['student.studentClass'])
            ->get();

        return InternshipGradeResource::collection($students);
    }

    /**
     * UC 41 - Bước 7-12: Thực hiện chấm điểm
     */
    public function submitGrade(GradeInternshipRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = auth()->user();
            $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();

            // BR-1: Chỉ giảng viên hướng dẫn mới có quyền chấm
            $internship = Internship::where('lecturer_id', $lecturerId)->findOrFail($id);

            // BR-3: Điểm đã gửi thành công không được phép chỉnh sửa
            if (!is_null($internship->university_grade)) {
                return response()->json(['message' => 'Điểm số đã được ghi nhận trước đó và không thể chỉnh sửa (BR-3).'], 400);
            }

            // Bước 9: Cập nhật điểm thi và nhận xét
            $internship->university_grade = $request->university_grade;
            $internship->university_feedback = $request->feedback;

            // Bước 10: Tính toán điểm cuối cùng
            $finalGrade = ($internship->company_grade + $request->university_grade) / 2;

            // Bước 11: Cập nhật trạng thái thực tập dựa trên điểm
            $internship->status = ($finalGrade >= 4) ? 'COMPLETED' : 'FAILED';
            $internship->updated_at = Carbon::now();
            $internship->save();

            // Bước 12: Gửi thông báo cho sinh viên
            $notification = Notification::create([
                'title' => 'Thông báo kết quả điểm thực tập',
                'content' => "Kết quả thực tập của bạn đã có. Điểm tổng kết: {$finalGrade}. Trạng thái: {$internship->status}."
            ]);

            UserNotification::create([
                'notification_id' => $notification->notification_id,
                'user_id' => $internship->student_id,
                'role_id' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chấm điểm thành công (Bước 12)',
                'data' => new InternshipGradeResource($internship)
            ]);
        });
    }
}
