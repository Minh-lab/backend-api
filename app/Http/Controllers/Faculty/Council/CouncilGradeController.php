<?php

namespace App\Http\Controllers\Faculty\Council;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculty\UpdateCouncilGradeRequest;
use App\Http\Resources\Faculty\Council\CapstoneCouncilGradeResource;
use App\Models\Capstone;
use App\Models\Council;
use App\Services\MilestoneService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CouncilGradeController extends Controller
{
    protected MilestoneService $milestoneService;
    protected NotificationService $notificationService;

    public function __construct(
        MilestoneService $milestoneService,
        NotificationService $notificationService
    ) {
        $this->milestoneService = $milestoneService;
        $this->notificationService = $notificationService;
    }

    /**
     * Lấy danh sách sinh viên đủ điều kiện bảo vệ trong một hội đồng
     * 
     * @param int $councilId
     * @return JsonResponse
     */
    public function showCapstones(int $councilId): JsonResponse
    {
        $council = Council::find($councilId);

        if (!$council) {
            return response()->json([
                'success' => false,
                'message' => 'Hội đồng không tồn tại',
            ], 404);
        }

        // Lấy danh sách capstone trong hội đồng với status DEFENSE_ELIGIBLE
        $capstones = Capstone::where('council_id', $councilId)
            ->where('status', Capstone::STATUS_DEFENSE_ELIGIBLE)
            ->with(['student', 'topic'])
            ->orderBy('defense_order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Danh sách sinh viên đủ điều kiện bảo vệ',
            'data' => [
                'council' => [
                    'council_id' => $council->council_id,
                    'name' => $council->name,
                    'buildings' => $council->buildings,
                    'rooms' => $council->rooms,
                    'start_date' => $council->start_date,
                    'end_date' => $council->end_date,
                    'semester_id' => $council->semester_id,
                ],
                'capstones' => CapstoneCouncilGradeResource::collection($capstones),
                'total' => $capstones->count(),
            ]
        ]);
    }

    /**
     * Cập nhật điểm hội đồng cho sinh viên
     * 
     * @param int $councilId
     * @param int $capstoneId
     * @param UpdateCouncilGradeRequest $request
     * @return JsonResponse
     */
    public function updateGrade(int $councilId, int $capstoneId, UpdateCouncilGradeRequest $request): JsonResponse
    {
        // Kiểm tra hội đồng tồn tại
        $council = Council::find($councilId);
        if (!$council) {
            return response()->json([
                'success' => false,
                'message' => 'Hội đồng không tồn tại',
            ], 404);
        }

        // Kiểm tra capstone tồn tại và thuộc hội đồng này
        $capstone = Capstone::where('capstone_id', $capstoneId)
            ->where('council_id', $councilId)
            ->with(['student', 'topic', 'semester'])
            ->first();

        if (!$capstone) {
            return response()->json([
                'success' => false,
                'message' => 'Sinh viên không tồn tại trong hội đồng này',
            ], 404);
        }

        // Kiểm tra xem còn thời gian nhập điểm hay không
        Log::info('CouncilGrade updateGrade - debug', [
            'councilId' => $councilId,
            'capstoneId' => $capstoneId,
            'semester_id' => $capstone->semester_id,
            'capstone_data' => $capstone->toArray()
        ]);
        
        $milestone = $this->milestoneService->getMilestone('Chấm điểm đồ án', $capstone->semester_id);
        
        Log::info('CouncilGrade milestone result', [
            'found' => $milestone ? true : false,
            'milestone_data' => $milestone ? $milestone->toArray() : null
        ]);

        if (!$milestone) {
            return response()->json([
                'success' => false,
                'message' => 'Mốc thời gian nhập điểm không tồn tại',
            ], 404);
        }

        // Check nếu ngoài thời gian nhập điểm
        if (!$this->milestoneService->isMilestoneActive('Chấm điểm đồ án', $capstone->semester_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Thời gian nhập điểm đã hết. Không thể cập nhật điểm.',
                'data' => [
                    'phase_name' => $milestone->phase_name,
                    'start_date' => $milestone->start_date->format('Y-m-d H:i:s'),
                    'end_date' => $milestone->end_date->format('Y-m-d H:i:s'),
                ]
            ], 403);
        }

        // Cập nhật điểm
        $councilGrade = (float) $request->input('council_grade');
        $capstone->council_grade = $councilGrade;
        $capstone->save();

        // Gửi thông báo cho sinh viên
        $this->sendNotificationToStudent($capstone, $councilGrade);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật điểm thành công',
            'data' => new CapstoneCouncilGradeResource($capstone),
        ]);
    }

    /**
     * Gửi thông báo cho sinh viên khi cập nhật điểm hội đồng
     * 
     * @param Capstone $capstone
     * @param float $grade
     * @return void
     */
    private function sendNotificationToStudent(Capstone $capstone, float $grade): void
    {
        try {
            $this->notificationService->sendToUser(
                'Đã cập nhật điểm bảo vệ',
                'Điểm hội đồng bảo vệ đề tài "' . $capstone->topic->title . '" của bạn là: ' . $grade,
                $capstone->student_id,
                4 // Role Student
            );
        } catch (\Exception $e) {
            Log::error('Error sending notification: ' . $e->getMessage());
        }
    }
}
