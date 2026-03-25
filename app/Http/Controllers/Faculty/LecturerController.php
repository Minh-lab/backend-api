<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Http\Resources\Faculty\LecturerListResource;
use App\Models\Lecturer;
use Illuminate\Http\JsonResponse;

class LecturerController extends Controller
{
    /**
     * Lấy danh sách giảng viên với thông tin trạng thái
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $lecturers = Lecturer::with([
            'expertises',
            'leaves' => function ($q) {
                $q->whereIn('lecturer_leaves.status', ['LEAVE_ACTIVE', 'APPROVED_PENDING']);
            },
            'requests' => function ($q) {
                $q->where('type', 'LEAVE_REQ')->where('status', 'PENDING');
            }
        ])->get();

        return response()->json([
            'success' => true,
            'message' => 'Danh sách giảng viên',
            'data' => LecturerListResource::collection($lecturers),
            'total' => $lecturers->count(),
        ]);
    }
}
