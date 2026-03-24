<?php

namespace App\Http\Controllers\Capstone;

use App\Http\Controllers\Controller;

use App\Models\Topic;
use App\Models\Capstone;
use App\Models\Lecturer;
use App\Models\CapstoneRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CapstoneRequestController extends Controller
{
    // UC17: Đăng ký đợt đồ án
    // POST /capstonerequest/register-capstone
    public function registerCapstone(Request $request)
    {
        $studentId = auth()->id();

        $request->merge(['student_id' => $studentId]);

        $request->validate([
            'topic_id'   => 'nullable|exists:topics,topic_id',
            'student_id' => 'required|exists:students,student_id',
        ]);

        $milestone = \App\Models\Milestone::where('type', \App\Models\Milestone::TYPE_CAPSTONE)->upcoming()->first();
        $semester_id = $milestone ? $milestone->semester_id : null;
        
        if (!$semester_id) {
            $semester = \App\Models\Semester::latest('start_date')->first();
            $semester_id = $semester ? $semester->semester_id : 1;
        }

        // Xóa đồ án cũ bị hủy/đang chờ hủy/thất bại để sv có thể test đăng ký lại
        Capstone::where('student_id', $request->student_id)
            ->where('semester_id', $semester_id)
            ->whereIn('status', [Capstone::STATUS_CANCEL, 'PENDING_CANCEL', Capstone::STATUS_FAILED])
            ->delete();

        $alreadyRegistered = Capstone::where('student_id', $request->student_id)
            ->where('semester_id', $semester_id)
            ->exists();

        if ($alreadyRegistered) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đăng ký đợt đồ án trong học kỳ này.'
            ], 400);
        }

        $capstone = Capstone::create([
            'topic_id'    => $request->topic_id ?? null,
            'student_id'  => $studentId,
            'semester_id' => $semester_id,
            'status'      => Capstone::STATUS_INITIALIZED,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'capstone' => $capstone,
            ]
        ], 201);
    }

    // UC18: Đăng ký GVHD DA
    // POST /capstonerequest/register-lecturer
    public function registerLecturer(Request $request)
    {
        $validated = $request->validate([
            'capstone_id'     => 'required|exists:capstones,capstone_id',
            'lecturer_id'     => 'required|exists:lecturers,lecturer_id',
            'student_message' => 'nullable|string|max:1000',
            'file'            => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $studentId = auth()->id();
        
        // Lấy capstone của sinh viên
        $capstone = Capstone::where('capstone_id', $validated['capstone_id'])
            ->where('student_id', $studentId)
            ->firstOrFail();

        // Kiểm tra sinh viên có GVHD hoặc yêu cầu đang chờ duyệt chưa
        $existingRequest = CapstoneRequest::where('capstone_id', $validated['capstone_id'])
            ->where('type', CapstoneRequest::TYPE_LECTURER_REG)
            ->whereIn('status', [
                CapstoneRequest::STATUS_PENDING_TEACHER,
                CapstoneRequest::STATUS_PENDING_FACULTY,
                CapstoneRequest::STATUS_APPROVED
            ])
            ->exists();
            
        if ($existingRequest || $capstone->lecturer_id) {
            return response()->json([
                'message' => 'Bạn đã đăng ký hoặc đã có giảng viên hướng dẫn rồi.',
            ], 422);
        }

        // Kiểm tra giảng viên có được đăng ký không
        $lecturer = Lecturer::where('lecturer_id', $validated['lecturer_id'])
            ->where('is_active', true)
            ->first();

        if (!$lecturer) {
            return response()->json([
                'message' => 'Giảng viên không còn khả dụng, vui lòng chọn giảng viên khác.',
            ], 422);
        }

        // Kiểm tra giảng viên có đang nghỉ phép không
        if ($lecturer->is_on_leave) {
            return response()->json([
                'message' => 'Giảng viên đang trong thời gian nghỉ phép, vui lòng chọn giảng viên khác.',
            ], 422);
        }


        // Xử lý file nếu có
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('capstone_requests', 'public');
        }

        // Tạo request để chờ giảng viên duyệt
        $capstoneRequest = CapstoneRequest::create([
            'capstone_id'     => $validated['capstone_id'],
            'lecturer_id'     => $validated['lecturer_id'],
            'type'            => CapstoneRequest::TYPE_LECTURER_REG,
            'status'          => \App\Models\CapstoneRequest::STATUS_PENDING_TEACHER,
            'student_message' => $validated['student_message'] ?? null,
            'file_path'       => $filePath,
        ]);

        $capstoneRequest->load('lecturer');

        return response()->json([
            'message' => 'Gửi yêu cầu đăng ký GVHD thành công.',
            'data'    => [
                'capstone_request_id' => $capstoneRequest->capstone_request_id,
                'capstone_id'         => $capstoneRequest->capstone_id,
                'type'                => $capstoneRequest->type,
                'status'              => $capstoneRequest->status,
                'student_message'     => $capstoneRequest->student_message,
                'file_path'           => $capstoneRequest->file_path
                    ? asset('storage/' . $capstoneRequest->file_path)
                    : null,
                'lecturer'            => $capstoneRequest->lecturer ? [
                    'lecturer_id' => $capstoneRequest->lecturer->lecturer_id,
                    'usercode'    => $capstoneRequest->lecturer->usercode,
                    'full_name'   => $capstoneRequest->lecturer->full_name,
                    'email'       => $capstoneRequest->lecturer->email,
                    'degree'      => $capstoneRequest->lecturer->degree,
                ] : null,
                'created_at'          => $capstoneRequest->created_at?->format('d/m/Y H:i'),
            ],
        ], 201);
    }

    // UC19: Đăng ký đề tài mới
    // POST /capstone-requests/register-new-topic
    public function registerTopic(Request $request)
    {
        //
        $validated = $request->validate([
            'capstone_id' => 'required|exists:capstones,capstone_id',
            'topic_id'    => 'required|exists:topics,topic_id',
        ]);

        // Tạo request mới
        $capstoneRequest = CapstoneRequest::create([
            'capstone_id' => $validated['capstone_id'],
            'topic_id'    => $validated['topic_id'],
            'type'        => CapstoneRequest::TYPE_TOPIC_PROP,
            'status'      => CapstoneRequest::STATUS_PENDING_FACULTY,
        ]);
    }

    // UC20: Đăng ký đề tài từ ngân hàng
    // POST /capstone-requests/register-topic-bank
    public function registerTopicFromBank(Request $request)
    {
        $validated = $request->validate([
            'capstone_id' => 'required|exists:capstones,capstone_id',
            'topic_id'    => 'required|exists:topics,topic_id',
        ]);

        // Tạo request mới
        $capstoneRequest = CapstoneRequest::create([
            'capstone_id' => $validated['capstone_id'],
            'topic_id'    => $validated['topic_id'],
            'type'        => CapstoneRequest::TYPE_TOPIC_BANK,
            'status'      => CapstoneRequest::STATUS_PENDING_FACULTY,
        ]);
    }
}
