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
        $council = Council::with(['members', 'capstones'])->find($councilId);

        if (!$council) {
            return response()->json([
                'success' => false,
                'message' => 'Hội đồng không tồn tại',
            ], 404);
        }

        $members = $council->members()->get();

        // Transform members to include review count
        $membersWithCount = $members->map(function ($member) {
            $reviewCount = \App\Models\CapstoneReviewer::where('lecturer_id', $member->lecturer_id)->count();
            return [
                'lecturer_id' => $member->lecturer_id,
                'name' => $member->full_name,
                'degree' => $member->degree,
                'department' => $member->department,
                'position' => $member->pivot?->position ?? null,
                'review_count' => $reviewCount,  // Count of reviews this lecturer has done
            ];
        });

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
                    'defense_dates' => [
                        'start_date' => $council->start_date,
                        'end_date' => $council->end_date,
                    ],
                ],
                'members' => $membersWithCount,
                'total' => $members->count(),
            ]
        ]);
    }

    /**
     * Phân công hội đồng và 2 giảng viên phản biện cho nhiều sinh viên (đồ án)
     * 
     * @return JsonResponse
     */
    public function assignCouncilAndReviewers(): JsonResponse
    {
        $councilId = request()->input('council_id');
        $reviewerIds = request()->input('reviewer_ids', []);
        $capstoneIds = request()->input('student_ids', []);

        // Validate inputs
        if (!$councilId || empty($reviewerIds) || empty($capstoneIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ: council_id, reviewer_ids, student_ids bắt buộc'
            ], 422);
        }

        // Check council exists
        $council = Council::find($councilId);
        if (!$council) {
            return response()->json([
                'success' => false,
                'message' => 'Hội đồng không tồn tại'
            ], 404);
        }

        try {
            // Update all capstones with council_id
            $updatedCount = \App\Models\Capstone::whereIn('capstone_id', $capstoneIds)
                ->update(['council_id' => $councilId]);

            if ($updatedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sinh viên (đồ án) nào'
                ], 404);
            }

            // Get all capstones to add reviewers
            $capstones = \App\Models\Capstone::whereIn('capstone_id', $capstoneIds)->get();

            // For each capstone, add reviewer assignments (delete old ones first)
            foreach ($capstones as $capstone) {
                // Delete existing reviewers for this capstone
                \App\Models\CapstoneReviewer::where('capstone_id', $capstone->capstone_id)->delete();

                // Add new reviewers
                foreach ($reviewerIds as $reviewerId) {
                    \App\Models\CapstoneReviewer::create([
                        'capstone_id' => $capstone->capstone_id,
                        'lecturer_id' => $reviewerId,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Phân công phản biện thành công cho {$updatedCount} sinh viên",
                'data' => [
                    'assigned_count' => $updatedCount,
                    'reviewer_count' => count($reviewerIds),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi phân công: ' . $e->getMessage()
            ], 500);
        }
    }
}
