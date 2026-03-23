<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use Illuminate\Http\JsonResponse;

class ClassController extends Controller
{
    /**
     * Lấy danh sách tất cả các lớp học
     * GET /admin/classes
     */
    public function index(): JsonResponse
    {
        try {
            $classes = Classes::select('class_id', 'class_name', 'lecturer_id', 'major_id')
                ->with(['lecturer:lecturer_id,full_name', 'major:major_id,major_name'])
                ->orderBy('class_name', 'asc')
                ->get();

            // Format dữ liệu trả về
            $formattedClasses = $classes->map(function ($class) {
                return [
                    'class_id' => $class->class_id,
                    'class_name' => $class->class_name,
                    'lecturer_id' => $class->lecturer_id,
                    'lecturer_name' => $class->lecturer?->full_name ?? 'N/A',
                    'major_id' => $class->major_id,
                    'major_name' => $class->major?->major_name ?? 'N/A',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedClasses,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách lớp học: ' . $e->getMessage(),
            ], 500);
        }
    }
}
