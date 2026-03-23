<?php

namespace App\Http\Controllers\Internship;

use App\Models\Internship;
use App\Http\Resources\Internship\InternshipSearchResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class InternshipStatisticsController extends InternshipBaseController
{
    /**
     * UC 44 - Bước 5: Lấy thống kê quản lý thực tập
     * GET /faculty_staff/internships/statistics
     * 
     * @param Request $request - Chứa filters: semester_id, status, lecturer_id, company_id
     */
    public function getStatistics(Request $request)
    {
        try {
            // Base query with eager loading
            $baseQuery = Internship::with([
                'student.studentClass',
                'company',
                'lecturer',
                'semester'
            ]);
            
            // Áp dụng filters
            if ($request->filled('semester_id') && $request->input('semester_id') !== 'all') {
                $baseQuery->where('semester_id', $request->input('semester_id'));
            }
            
            if ($request->filled('status') && $request->input('status') !== 'all') {
                $baseQuery->where('status', $request->input('status'));
            }
            
            if ($request->filled('lecturer_id') && $request->input('lecturer_id') !== 'all') {
                $baseQuery->where('lecturer_id', $request->input('lecturer_id'));
            }
            
            if ($request->filled('company_id') && $request->input('company_id') !== 'all') {
                $baseQuery->where('company_id', $request->input('company_id'));
            }
            
            // Pagination
            $page = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);
            
            // Get filtered data
            $allInternships = (clone $baseQuery)->get();
            $paginatedInternships = $baseQuery->orderBy('internship_id', 'desc')
                ->forPage($page, $perPage)
                ->get();
            
            // Calculate statistics - từ filtered data
            $statistics = [
                'total_students' => $allInternships->count(),
                'total_completed' => $allInternships->where('status', 'COMPLETED')->count(),
                'total_failed' => $allInternships->where('status', 'FAILED')->count(),
                'total_incompleted' => $allInternships->whereNotIn('status', ['COMPLETED', 'FAILED', 'CANCEL'])->count(),
                'total_cancelled' => $allInternships->where('status', 'CANCEL')->count(),
                'avg_score' => $allInternships->filter(fn($i) => !is_null($i->university_grade))
                    ->avg(fn($i) => ($i->company_grade + $i->university_grade) / 2) ?? 0,
            ];
            
            // Transform data
            $students = $paginatedInternships->map(function ($internship) {
                return [
                    'internship_id' => $internship->internship_id,
                    'id' => $internship->internship_id, // For compatibility
                    'name' => $internship->student->full_name ?? 'N/A',
                    'class' => $internship->student->studentClass->class_name ?? 'N/A',
                    'status' => $internship->status ?? 'INITIALIZED',
                    'lecturer' => $internship->lecturer->full_name ?? '---',
                    'enterprise' => $internship->company->name ?? '---',
                    'process_score' => $internship->company_grade ?? 0,
                    'exam_score' => $internship->university_grade,
                    'final_score' => !is_null($internship->university_grade) && !is_null($internship->company_grade) 
                        ? ($internship->company_grade + $internship->university_grade) / 2 
                        : null,
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $statistics,
                    'students' => $students,
                    'pagination' => [
                        'total' => $allInternships->count(),
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => ceil($allInternships->count() / $perPage),
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy thống kê: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * UC 44 - Bước 7: Xuất báo cáo Excel
     * GET /faculty_staff/internships/export
     * 
     * @param Request $request - Filters giống như getStatistics
     */
    public function exportReport(Request $request)
    {
        try {
            // Base query
            $query = Internship::with([
                'student.studentClass',
                'company',
                'lecturer',
                'semester'
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
            
            if ($request->filled('company_id') && $request->input('company_id') !== 'all') {
                $query->where('company_id', $request->input('company_id'));
            }
            
            // Get all data (not paginated for export)
            $internships = $query->orderBy('internship_id', 'desc')->get();
            
            // Create CSV content
            $headers = [
                'STT',
                'MSSV',
                'Tên Sinh Viên',
                'Lớp',
                'Doanh Nghiệp',
                'GVHD',
                'Điểm Quá Trình',
                'Điểm Thi',
                'Điểm Tổng Kết',
                'Trạng Thái'
            ];
            
            $rows = [];
            foreach ($internships as $index => $internship) {
                $finalScore = !is_null($internship->university_grade) && !is_null($internship->company_grade)
                    ? ($internship->company_grade + $internship->university_grade) / 2
                    : 'N/A';
                
                $rows[] = [
                    $index + 1,
                    $internship->student->usercode ?? 'N/A',
                    $internship->student->full_name ?? 'N/A',
                    $internship->student->studentClass->class_name ?? 'N/A',
                    $internship->company->name ?? '---',
                    $internship->lecturer->full_name ?? '---',
                    $internship->company_grade ?? 0,
                    $internship->university_grade ?? 'Chưa chấm',
                    $finalScore,
                    $internship->status ?? 'INITIALIZED',
                ];
            }
            
            // Generate CSV
            $output = fopen('php://output', 'w');
            
            // Set headers for download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="internship_report_' . date('Y-m-d_H-i-s') . '.csv"');
            
            // Write BOM for Excel to recognize UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write CSV
            fputcsv($output, $headers);
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xuất báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * UC 44: Lấy danh sách học kỳ để lọc
     * GET /faculty_staff/internships/semesters
     */
    public function getSemesters()
    {
        try {
            $semesters = \App\Models\Semester::with('academicYear')
                ->orderBy('start_date', 'desc')
                ->get()
                ->map(function ($semester) {
                    return [
                        'semester_id' => $semester->semester_id,
                        'semester_name' => $semester->semester_name,
                        'year_name' => $semester->academicYear->year_name ?? '',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $semesters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách học kỳ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * UC 44: Lấy danh sách giảng viên để lọc
     * GET /faculty_staff/internships/filter/lecturers
     */
    public function getLecturersForFilter()
    {
        try {
            $lecturers = \App\Models\Lecturer::where('is_active', 1)
                ->orderBy('full_name', 'asc')
                ->get()
                ->map(function ($lecturer) {
                    return [
                        'lecturer_id' => $lecturer->lecturer_id,
                        'full_name' => $lecturer->full_name,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $lecturers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách giảng viên: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * UC 44: Lấy danh sách doanh nghiệp để lọc
     * GET /faculty_staff/internships/filter/companies
     */
    public function getCompaniesForFilter()
    {
        try {
            $companies = \App\Models\Company::where('is_active', 1)
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($company) {
                    return [
                        'company_id' => $company->company_id,
                        'name' => $company->name,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $companies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách doanh nghiệp: ' . $e->getMessage()
            ], 500);
        }
    }
}
