<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Http\Resources\Faculty\LecturerListResource;
use App\Models\Lecturer;
use Illuminate\Http\JsonResponse;

class LecturerController extends Controller
{
    /**
     * Lấy danh sách giảng viên
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $lecturers = Lecturer::with('expertises')->get();

        return response()->json([
            'success' => true,
            'message' => 'Danh sách giảng viên',
            'data' => LecturerListResource::collection($lecturers),
            'total' => $lecturers->count(),
        ]);
    }
}
