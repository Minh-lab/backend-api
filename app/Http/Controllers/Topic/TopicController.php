<?php

namespace App\Http\Controllers\Topic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Topic\TopicRequest;
use App\Http\Resources\TopicResource;

use App\Models\Topic;
use App\Models\Expertise;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopicController extends Controller
{

    // UC-13: Tìm kiếm đề tài
    // GET /topics?keyword=&technology=&description=&expertise_id=&page=&per_page=
    public function index(Request $request)
    {
        $keyword = trim($request->query('keyword', ''));
        $technology = trim($request->query('technology', ''));
        $description = trim($request->query('description', ''));
        $expertiseId = $request->query('expertise_id');
        $perPage = max(1, min((int)$request->query('per_page', 10), 100));

        $query = Topic::with('expertise')->select(
            'topic_id',
            'expertise_id',
            'lecturer_id',
            'faculty_staff_id',
            'title',
            'description',
            'technologies',
            'is_available',
            'is_bank_topic',
            'created_at'
        );

        // Filter by current user if they're accessing via /lecturer route
        // Lecturers only see their own topics
        // Faculty staff can see all topics
        $user = Auth::user();
        if ($user && get_class($user) === \App\Models\Lecturer::class) {
            // Lecturers only see their own topics
            $query->where('lecturer_id', $user->lecturer_id);
        }

        // Tìm theo keyword (title)
        if (strlen($keyword) >= 2) {
            $escaped = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $keyword);
            $query->where('title', 'like', "%{$escaped}%");
        }

        // Tìm theo công nghệ
        if (strlen($technology) >= 2) {
            $escaped = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $technology);
            $query->where('technologies', 'like', "%{$escaped}%");
        }

        // Tìm theo mô tả (chính xác)
        if ($description !== '') {
            $query->where('description', $description);
        }

        // Lọc theo expertise (FK)
        if ($expertiseId) {
            $query->where('expertise_id', $expertiseId);
        }

        // Eager load relationships
        $query->with(['expertise', 'lecturer', 'facultyStaff']);

        $topics = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => TopicResource::collection($topics->items()),
            'meta' => [
                'total' => $topics->total(),
                'page' => $topics->currentPage(),
                'per_page' => $topics->perPage(),
                'last_page' => $topics->lastPage(),
            ]
        ]);
    }

    // UC-14: Thêm đề tài
    // POST /topics
    public function store(TopicRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Auto-set lecturer_id or faculty_staff_id based on user type
        $user = Auth::user();
        if ($user && get_class($user) === \App\Models\Lecturer::class) {
            $data['lecturer_id'] = $user->lecturer_id;
            $data['faculty_staff_id'] = null;
        }
        else if ($user && get_class($user) === \App\Models\FacultyStaff::class) {
            $data['faculty_staff_id'] = $user->faculty_staff_id;
            $data['lecturer_id'] = null;
        }

        $topic = Topic::create($data);

        // Load relationships
        $topic->load(['expertise', 'lecturer', 'facultyStaff']);

        return response()->json([
            'success' => true,
            'message' => 'Thêm đề tài thành công',
            'data' => TopicResource::make($topic)
        ], 201);
    }

    // UC-15: Sửa đề tài
    // PUT /topics/{id}
    public function update(TopicRequest $request, $id): JsonResponse
    {
        $data = $request->validated();

        $topic = Topic::findOrFail($id);

        // Check authorization - lecturers can only edit their own topics
        // Faculty staff can edit any topic
        $user = Auth::user();
        if ($user && get_class($user) === \App\Models\Lecturer::class && $topic->lecturer_id !== $user->lecturer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền sửa đề tài này'
            ], 403);
        }

        $topic->update($data);

        // Load relationships
        $topic->load(['expertise', 'lecturer', 'facultyStaff']);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật đề tài thành công',
            'data' => TopicResource::make($topic)
        ]);
    }

    // UC-16: Xóa đề tài
    // DELETE /topics/{id}
    public function destroy(string $id)
    {
        $topic = Topic::findOrFail($id);

        // Check authorization - lecturers can only delete their own topics
        // Faculty staff can delete any topic
        $user = Auth::user();
        if ($user && get_class($user) === \App\Models\Lecturer::class && $topic->lecturer_id !== $user->lecturer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa đề tài này'
            ], 403);
        }

        try {
            $topic->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // Check if it's a foreign key constraint violation
            if ($e->getCode() === '23000' || strpos($e->getMessage(), '1451') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa đề tài này',
                    'detail' => 'Đề tài đang được sử dụng bởi một hoặc nhiều hồ sơ. Vui lòng kiểm tra xem có sinh viên nào đang sử dụng đề tài này không.'
                ], 409);
            }
            // Re-throw if it's not a constraint violation
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Xóa đề tài thành công'
        ]);
    }
}
