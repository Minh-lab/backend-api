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
        $request->validate([
            'topic_id'    => 'required|exists:topics,topic_id',
            'student_id'  => 'required|exists:students,student_id',
            'semester_id' => 'required|exists:semesters,semester_id',
        ]);

        $capstone = Capstone::create([
            'topic_id'    => $request->topic_id,
            'student_id'  => $request->student_id,
            'semester_id' => $request->semester_id,
            'status'      => Capstone::STATUS_INITIALIZED,
        ]);

        $capstoneRequest = CapstoneRequest::create([
            'capstone_id' => $capstone->capstone_id,
            'type'        => CapstoneRequest::TYPE_LECTURER_REG,
            'status'      => CapstoneRequest::STATUS_PENDING_TEACHER,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'capstone'         => $capstone,
                'capstone_request' => $capstoneRequest,
            ]
        ], 201);
    }

    // UC18: Đăng ký GVHD DA
    // POST /capstonerequest/register-lecturer
    public function registerLecturer(Request $request)
    {
        $validated = $request->validate([
            'capstone_id'     => 'required|exists:capstones,capstone_id',
            'student_id'      => 'required|exists:students,student_id',
            'lecturer_id'     => 'required|exists:lecturers,lecturer_id',
            'student_message' => 'nullable|string|max:1000',
            'file'            => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        // Lấy capstone của sinh viên
        $capstone = Capstone::where('capstone_id', $validated['capstone_id'])
            ->where('student_id', $validated['student_id'])
            ->firstOrFail();

        // Kiểm tra sinh viên có GVHD chưa
        $hasLecturer = CapstoneRequest::where('capstone_id', $validated['capstone_id'])
            ->where('type', CapstoneRequest::TYPE_LECTURER_REG)
            ->where('status', CapstoneRequest::STATUS_APPROVED)
            ->exists();

        if ($hasLecturer) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đăng ký giảng viên hướng dẫn đồ án.',
            ], 422);
        }

        // Kiểm tra giảng viên có khả dụng không
        $lecturer = Lecturer::where('lecturer_id', $validated['lecturer_id'])
            ->where('is_active', true)
            ->where('is_on_leave', false)
            ->first();

        if (!$lecturer) {
            return response()->json([
                'success' => false,
                'message' => 'Giảng viên không khả dụng, vui lòng chọn giảng viên khác.',
            ], 422);
        }

        // Xử lý file nếu có
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('capstone_requests', 'public');
        }

        // Tạo request mới
        $capstoneRequest = CapstoneRequest::create([
            'capstone_id'     => $validated['capstone_id'],
            'lecturer_id'     => $validated['lecturer_id'],
            'type'            => CapstoneRequest::TYPE_LECTURER_REG,
            'status'          => CapstoneRequest::STATUS_PENDING_TEACHER,
            'student_message' => $validated['student_message'] ?? null,
            'file_path'       => $filePath,
        ]);

        $capstoneRequest->load('lecturer');

        return response()->json([
            'success' => true,
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
                'created_at' => $capstoneRequest->created_at?->format('d/m/Y H:i'),
            ],
        ], 201);
    }

    // UC19: Đăng ký đề tài mới
    // POST /capstone-requests/register-new-topic
    public function registerTopic(Request $request)
    {
        $validated = $request->validate([
            'capstone_id' => 'required|exists:capstones,capstone_id',
            'topic_id'    => 'required|exists:topics,topic_id',
        ]);

        $capstoneRequest = CapstoneRequest::create([
            'capstone_id' => $validated['capstone_id'],
            'topic_id'    => $validated['topic_id'],
            'type'        => CapstoneRequest::TYPE_TOPIC_PROP,
            'status'      => CapstoneRequest::STATUS_PENDING_FACULTY,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký đề tài mới thành công.',
            'data'    => $capstoneRequest,
        ], 201);
    }

    // UC20: Đăng ký đề tài từ ngân hàng
    // POST /capstone-requests/register-topic-bank
    public function registerTopicFromBank(Request $request)
    {
        $validated = $request->validate([
            'capstone_id' => 'required|exists:capstones,capstone_id',
            'topic_id'    => 'required|exists:topics,topic_id',
        ]);

        $capstoneRequest = CapstoneRequest::create([
            'capstone_id' => $validated['capstone_id'],
            'topic_id'    => $validated['topic_id'],
            'type'        => CapstoneRequest::TYPE_TOPIC_BANK,
            'status'      => CapstoneRequest::STATUS_PENDING_FACULTY,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký đề tài từ ngân hàng thành công.',
            'data'    => $capstoneRequest,
        ], 201);
    }
}
