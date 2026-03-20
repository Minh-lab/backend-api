<?php

namespace App\Http\Controllers\Expertise;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpertiseResource;
use App\Models\Expertise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpertiseController extends Controller
{
    // Lấy danh sách chuyên ngành
    // GET /expertises
    public function index(): JsonResponse
    {
        $expertises = Expertise::all();

        return response()->json([
            'success' => true,
            'data'    => ExpertiseResource::collection($expertises),
        ]);
    }
}
