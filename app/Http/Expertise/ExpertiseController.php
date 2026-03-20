<?php

namespace App\Http\Expertise;

use App\Models\Expertise;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ExpertiseController extends Controller
    {
        /**
         * Lấy danh sách tất cả chuyên môn
         * 
         * @return JsonResponse
         */
        public function index(): JsonResponse
        {
            try {
                $expertises = Expertise::all();
    
                return response()->json([
                    'success' => true,
                    'message' => 'Danh sách chuyên môn',
                    'data' => $expertises,
                    'total' => $expertises->count(),
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi khi lấy danh sách chuyên môn: ' . $e->getMessage(),
                ], 500);
            }
        }
}
