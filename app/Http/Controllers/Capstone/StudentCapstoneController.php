<?php

namespace App\Http\Controllers\Capstone;

use App\Models\{Capstone, CapstoneRequest, Topic};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentCapstoneController extends CapstoneBaseController
{
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
            $semesterId = $this->resolveCurrentSemesterId();
            if (!$semesterId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Khong tim thay hoc ky de de xuat de tai.'
                ], 400);
            }

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
                'semester_id' => $semesterId,
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
            $semesterId = $this->resolveCurrentSemesterId();
            if (!$semesterId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Khong tim thay hoc ky de dang ky de tai.'
                ], 400);
            }

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
                'lecturer_id' => $validated['lecturer_id'],
                'semester_id' => $semesterId,
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
                'has_pending_lecturer_request' => $capstone->requests()
                    ->where('type', 'LECTURER_REG')
                    ->whereIn('status', ['PENDING_TEACHER', 'PENDING_FACULTY', 'APPROVED'])
                    ->exists(),
                'created_at' => $capstone->created_at,
            ],
        ]);
    }
}
