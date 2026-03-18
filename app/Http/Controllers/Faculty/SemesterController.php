<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculty\StoreSemesterRequest;
use App\Http\Resources\SemesterResource;
use App\Models\AcademicYear;
use App\Models\Milestone;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    /**
     * UC: Thêm năm học và học kỳ mới
     * Chỉ Văn phòng Khoa (faculty_staff) mới được phép thực hiện.
     */
    public function store(StoreSemesterRequest $request): JsonResponse
    {
        $yearName = $request->input('year_name');
        $semesterName = $request->input('semester_name');

        // Tìm hoặc tạo năm học theo year_name
        $academicYear = AcademicYear::firstOrCreate(
            ['year_name' => $yearName],
            [
                // Tự parse start_year/end_year từ chuỗi năm học (vd: "2024-2025")
                'start_year' => explode('-', $yearName)[0] ?? null,
                'end_year' => explode('-', $yearName)[1] ?? null,
            ]
        );

        $semester = Semester::create([
            'year_id' => $academicYear->year_id,
            'semester_name' => $semesterName,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm học kỳ thành công.',
            'data' => [
                'academic_year' => $academicYear,
                'semester' => $semester,
            ],
        ], 201);
    }
    /**
     * UC: Lấy danh sách tất cả học kỳ
     * Chỉ Văn phòng Khoa (faculty_staff) mới được phép thực hiện.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);

        $semesters = Semester::with('academicYear')
            ->join('academic_years', 'semesters.year_id', '=', 'academic_years.year_id')
            ->orderBy('academic_years.year_name', 'desc')
            ->orderBy('semesters.semester_name', 'desc')
            ->select('semesters.*')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách học kỳ thành công.',
            'data' => $semesters->items(),
            'meta' => [
                'total' => $semesters->total(),
                'page' => $semesters->currentPage(),
                'per_page' => $semesters->perPage(),
                'last_page' => $semesters->lastPage(),
            ],
        ], 200);
    }
    // UC - Lấy chi tiết 1 học kỳ (kèm year_name)
    // GET /semesters/{id}
    public function show($id): JsonResponse
    {
        $semester = Semester::with('academicYear')
            ->where('semester_id', $id)
            ->first();

        if (!$semester) {
            return response()->json([
                'success' => false,
                'message' => 'Học kỳ không tồn tại.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SemesterResource($semester),
        ], 200);
    }

    // UC - Danh sách milestone theo học kỳ (có phân trang)
    // GET /semesters/{id}/milestones?page=1&per_page=10
    public function milestones(Request $request, $id): JsonResponse
    {
        $semester = Semester::find($id);

        if (!$semester) {
            return response()->json([
                'success' => false,
                'message' => 'Học kỳ không tồn tại.',
            ], 404);
        }

        $perPage = (int) $request->query('per_page', 10);

        $milestones = Milestone::where('semester_id', $id)
            ->orderBy('start_date', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $milestones->items(),
            'meta' => [
                'total' => $milestones->total(),
                'page' => $milestones->currentPage(),
                'per_page' => $milestones->perPage(),
                'last_page' => $milestones->lastPage(),
            ],
        ], 200);
    }

    // UC - Lấy chi tiết 1 milestone
    // GET /semesters/{id}/milestones/{milestoneId}
    public function showMilestone($id, $milestoneId): JsonResponse
    {
        $semester = Semester::find($id);

        if (!$semester) {
            return response()->json([
                'success' => false,
                'message' => 'Học kỳ không tồn tại.',
            ], 404);
        }

        $milestone = Milestone::where('semester_id', $id)
            ->where('milestone_id', $milestoneId)
            ->first();

        if (!$milestone) {
            return response()->json([
                'success' => false,
                'message' => 'Milestone không tồn tại.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $milestone,
        ], 200);
    }

}
