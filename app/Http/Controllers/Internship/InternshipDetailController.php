<?php

namespace App\Http\Controllers\Internship;

use App\Models\Internship;
use App\Http\Resources\Internship\InternshipDetailResource;

class InternshipDetailController extends InternshipBaseController
{
    /**
     * GET /internships/{id}
     * Lấy chi tiết thông tin thực tập của một sinh viên
     * 
     * @param string $id - internship_id hoặc student_id
     */
    public function show($id)
    {
        $user = auth()->user();
        
        // Query internship với eager load relations
        $internship = Internship::with([
            'student.studentClass',
            'company',
            'lecturer',
            'semester',
            'reports'
        ])->find($id);
        
        if (!$internship) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin thực tập'
            ], 404);
        }
        
        // Kiểm tra quyền truy cập (Faculty có quyền xem tất cả, Lecturer chỉ xem của mình)
        if (!auth()->user()->roles->contains('role_name', 'faculty_staff')) {
            if (auth()->user()->roles->contains('role_name', 'lecturer')) {
                if ($internship->lecturer_id !== $user->lecturer_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn không có quyền truy cập thông tin này'
                    ], 403);
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => new InternshipDetailResource($internship)
        ]);
    }
}
