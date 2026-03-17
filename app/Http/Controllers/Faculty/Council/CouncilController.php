<?php

namespace App\Http\Controllers\Faculty\Council;

use App\Http\Controllers\Controller;
use App\Http\Resources\Faculty\Council\CouncilListResource;
use App\Http\Resources\Faculty\Council\CouncilMemberResource;
use App\Models\Council;
use Illuminate\Http\JsonResponse;

class CouncilController extends Controller
{
    /**
     * Lấy danh sách hội đồng có phân trang
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 15);

        $councils = Council::with(['semester', 'capstones'])
            ->withCount('capstones')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Danh sách hội đồng',
            'data' => CouncilListResource::collection($councils),
            'pagination' => [
                'current_page' => $councils->currentPage(),
                'per_page' => $councils->perPage(),
                'total' => $councils->total(),
                'last_page' => $councils->lastPage(),
            ]
        ]);
    }

    /**
     * Lấy danh sách giảng viên của hội đồng (council members)
     * 
     * @param int $councilId
     * @return JsonResponse
     */
    public function getMembers(int $councilId): JsonResponse
    {
        $council = Council::find($councilId);

        if (!$council) {
            return response()->json([
                'success' => false,
                'message' => 'Hội đồng không tồn tại',
            ], 404);
        }

        $members = $council->members()->get();

        return response()->json([
            'success' => true,
            'message' => 'Danh sách thành viên hội đồng',
            'data' => [
                'council' => [
                    'council_id' => $council->council_id,
                    'name' => $council->name,
                    'buildings' => $council->buildings,
                    'rooms' => $council->rooms,
                    'start_date' => $council->start_date,
                    'end_date' => $council->end_date,
                ],
                'members' => CouncilMemberResource::collection($members),
                'total' => $members->count(),
            ]
        ]);
    }
}
