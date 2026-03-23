<?php

namespace App\Http\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculty\CheckMilestoneRequest;
use App\Services\MilestoneService;
use Illuminate\Http\JsonResponse;

class MilestoneCheckController extends Controller
{
    protected MilestoneService $milestoneService;

    public function __construct(MilestoneService $milestoneService)
    {
        $this->milestoneService = $milestoneService;
    }

    /**
     * Kiểm tra xem thời gian hiện tại có nằm trong mốc thời gian nhập điểm hay không
     * 
     * @param CheckMilestoneRequest $request
     * @return JsonResponse
     */
    public function check(CheckMilestoneRequest $request): JsonResponse
    {
        $phaseName = $request->input('phase_name');
        $semesterId = $request->input('semester_id');

        $result = $this->milestoneService->checkMilestoneStatus($phaseName, $semesterId);

        $statusCode = $result['success'] ? 200 : 404;

        return response()->json($result, $statusCode);
    }
}
