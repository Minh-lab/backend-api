<?php

namespace App\Http\Controllers\Capstone;

use App\Models\Capstone;
use App\Models\Semester;
use App\Models\Lecturer;
use App\Models\Council;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatisticsController extends CapstoneBaseController
{
    /**
     * UC 32: Thống kê và lọc danh sách đồ án
     * Mặc định hiển thị TẤT CẢ capstones, chỉ filter khi user thay đổi
     */
    public function indexStatistics(Request $request)
    {
        try {
            // Khởi tạo query - lấy TẤT CẢ capstones (không filter theo semester)
            $query = Capstone::with([
                'student.studentClass',
                'lecturer',
                'reviewers.lecturer',
                'council'
            ]);

            // Áp dụng filters CHỈ khi được cung cấp
            if ($request->filled('semester_id') && $request->input('semester_id') !== 'all') {
                $query->where('semester_id', $request->input('semester_id'));
            }

            if ($request->filled('status') && $request->input('status') !== 'all') {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('lecturer_id') && $request->input('lecturer_id') !== 'all') {
                $query->where('lecturer_id', $request->input('lecturer_id'));
            }

            if ($request->filled('council_id') && $request->input('council_id') !== 'all') {
                $query->where('council_id', $request->input('council_id'));
            }

            if ($request->filled('reviewer_id') && $request->input('reviewer_id') !== 'all') {
                $query->whereHas('reviewers', function ($q) use ($request) {
                    $q->where('lecturer_id', $request->input('reviewer_id'));
                });
            }

            // Pagination
            $page = $request->input('page', 1);
            $perPage = $request->input('itemsPerPage', 10);
            
            $total = $query->count();
            
            // Tính thống kê - dựa vào FILTERED capstones (sau khi lọc)
            $filteredCapstones = $query->get();
            $statistics = [
                'total_capstones' => $filteredCapstones->count(),
                'completed' => $filteredCapstones->where('status', 'COMPLETED')->count(),
                'no_gvhd' => $filteredCapstones->whereNull('lecturer_id')->count(),
                'no_gvpb' => $filteredCapstones->filter(function ($capstone) {
                    return $capstone->reviewers->count() === 0;
                })->count(),
            ];
            
            // Lấy dữ liệu phân trang
            $capstones = $query->orderBy('capstone_id', 'desc')
                ->forPage($page, $perPage)
                ->get();

            // Transformasi capstones data
            $capstonesData = $capstones->map(function ($capstone) {
                // Lấy danh sách giáo viên phản biện, ngăn cách bằng dấu phẩy
                $gvpbNames = $capstone->reviewers->pluck('lecturer.full_name')->filter()->implode(', ') ?: null;
                
                return [
                    'capstone_id' => $capstone->capstone_id,
                    'student_code' => $capstone->student?->usercode ?? '---',
                    'student_name' => $capstone->student?->full_name ?? '---',
                    'class_name' => $capstone->student?->studentClass?->class_name ?? '---',
                    'status' => $capstone->status,
                    'lecturer_name' => $capstone->lecturer?->full_name ?? 'Chưa phân công',
                    'reviewers_name' => $gvpbNames ?? 'Chưa phân công',
                    'council_name' => $capstone->council?->name ?? '---',
                    'council_grade' => $capstone->council_grade ?? '---',
                ];
            })->toArray();
            return response()->json([
                'success' => true,
                'data' => [
                    'capstones' => $capstonesData,
                    'statistics' => $statistics,
                    'pagination' => [
                        'current_page' => $page,
                        'total_items' => $total,
                        'items_per_page' => $perPage,
                        'total_pages' => ceil($total / $perPage),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('StatisticsController error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy dữ liệu thống kê: ' . $e->getMessage(),
                'error_debug' => env('APP_DEBUG') ? [
                    'exception' => class_basename($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    /**
     * UC 32: Xuất báo cáo Excel (Logic chuẩn bị dữ liệu)
     */
    public function exportStatistics(Request $request)
    {
        // Lấy dữ liệu giống như indexStatistics nhưng không phân trang
        $query = Capstone::with([
            'student.studentClass',
            'lecturer',
            'reviewers.lecturer',
            'council'
        ]);

        // Áp dụng filters
        if ($request->filled('semester_id') && $request->input('semester_id') !== 'all') {
            $query->where('semester_id', $request->input('semester_id'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('lecturer_id') && $request->input('lecturer_id') !== 'all') {
            $query->where('lecturer_id', $request->input('lecturer_id'));
        }

        if ($request->filled('council_id') && $request->input('council_id') !== 'all') {
            $query->where('council_id', $request->input('council_id'));
        }

        // Lấy dữ liệu đã lọc
        $filteredCapstones = $query->orderBy('capstone_id', 'desc')->get();

        // Chuẩn bị dữ liệu cho export
        $exportData = $filteredCapstones->map(function ($capstone) {
            // Lấy danh sách giáo viên phản biện
            $gvpbNames = $capstone->reviewers->pluck('lecturer.full_name')->filter()->implode(', ') ?: 'Chưa phân công';
            
            return [
                'Mã sinh viên' => $capstone->student?->usercode ?? '---',
                'Tên sinh viên' => $capstone->student?->full_name ?? '---',
                'Lớp' => $capstone->student?->studentClass?->class_name ?? '---',
                'Trạng thái' => $capstone->status,
                'GVHD' => $capstone->lecturer?->full_name ?? 'Chưa phân công',
                'GVPB' => $gvpbNames,
                'Hội đồng' => $capstone->council?->name ?? '---',
                'Điểm GV' => $capstone->instructor_grade ?? '---',
                'Điểm HĐ' => $capstone->council_grade ?? '---',
            ];
        })->toArray();

        // Tính thống kê dựa vào FILTERED capstones
        $statistics = [
            'total_capstones' => $filteredCapstones->count(),
            'completed' => $filteredCapstones->where('status', 'COMPLETED')->count(),
            'no_gvhd' => $filteredCapstones->whereNull('lecturer_id')->count(),
            'no_gvpb' => $filteredCapstones->filter(function ($capstone) {
                return $capstone->reviewers->count() === 0;
            })->count(),
        ];

        // NFR-1: Tên file bao gồm ngày xuất
        $fileName = "Bao-cao-do-an-" . Carbon::now()->format('Ymd-His') . ".json";

        // Trả về dữ liệu để xuất (frontend có thể dùng thư viện như xlsx để export)
        return response()->json([
            'success' => true,
            'file_name' => $fileName,
            'data' => $exportData,
            'statistics' => $statistics,
            'message' => 'Dữ liệu sẵn sàng để xuất'
        ]);
    }

    /**
     * Lấy danh sách học kỳ (cho dropdown filter)
     */
    public function getSemesters()
    {
        try {
            $semesters = Semester::with('academicYear')
                ->orderBy('year_id', 'desc')
                ->orderBy('semester_id', 'desc')
                ->get()
                ->map(function ($semester) {
                    return [
                        'semester_id' => $semester->semester_id,
                        'semester_name' => $semester->semester_name,
                        'year_name' => $semester->academicYear?->year_name ?? ''
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $semesters
            ]);
        } catch (\Exception $e) {
            \Log::error('getSemesters error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách học kỳ'
            ], 500);
        }
    }

    /**
     * Lấy danh sách giảng viên (cho dropdown filter)
     */
    public function getLecturers()
    {
        try {
            $lecturers = Lecturer::select('lecturer_id', 'full_name')
                ->where('is_active', 1)
                ->orderBy('full_name')
                ->get()
                ->map(function ($lecturer) {
                    return [
                        'lecturer_id' => $lecturer->lecturer_id,
                        'full_name' => $lecturer->full_name
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $lecturers
            ]);
        } catch (\Exception $e) {
            \Log::error('getLecturers error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách giảng viên'
            ], 500);
        }
    }

    /**
     * Lấy danh sách hội đồng (cho dropdown filter)
     */
    public function getCouncils()
    {
        try {
            $councils = Council::select('council_id', 'name')
                ->orderBy('name')
                ->get()
                ->map(function ($council) {
                    return [
                        'council_id' => $council->council_id,
                        'name' => $council->name
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $councils
            ]);
        } catch (\Exception $e) {
            \Log::error('getCouncils error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách hội đồng'
            ], 500);
        }
    }
}