<?php

namespace App\Http\Expertise;

use App\Models\Expertise;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ExpertiseController extends Controller
{
    
     //Lấy danh sách toàn bộ chuyên môn trong hệ thống
     //UC: Hiển thị danh mục chuyên môn cho mọi đối tượng
     
    public function index(): JsonResponse
    {
        // Lấy tất cả và sắp xếp theo tên để Frontend hiển thị đẹp hơn
        $expertises = Expertise::orderBy('name')->get(['expertise_id', 'name', 'description']);

        return response()->json([
            "success" => true,
            "message" => "Lấy danh sách chuyên môn thành công.",
            "data"    => $expertises
        ], 200);
    }
}